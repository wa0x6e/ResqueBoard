<?php
/**
 * ResqueStat classes
 *
 * Contains all classes required to fetch static datas
 * from php-resque and the cube server
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        Wan Qi Chen <kami@kamisama.me>
 * @copyright     Copyright 2012, Wan Qi Chen <kami@kamisama.me>
 * @link          http://resqueboard.kamisama.me
 * @package       resqueboard
 * @subpackage      resqueboard.lib
 * @since         1.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\Lib;

/**
 * ResqueStat class
 *
 * Connect to the backend to retrieve php-resque datas, or metrics
 *
 * @subpackage      resqueboard.lib
 * @since            1.0.0
 * @author           Wan Qi Chen <kami@kamisama.me>
 */
class ResqueStat
{
    protected $stats = array();
    protected $workers = array();
    protected $queues = array();

    const JOB_STATUS_WAITING = 1;
    const JOB_STATUS_RUNNING = 2;
    const JOB_STATUS_FAILED = 3;
    const JOB_STATUS_COMPLETE = 4;

    private $settings;
    private $redis;
    private $mongo;

    /**
     * @since 1.0.0
     */
    public function __construct($settings = array())
    {
        $this->settings = array(
                'mongo' => array('host' => 'localhost', 'port' => 27017, 'database' => 'cube_development'),
                'redis' => array('host' => '127.0.0.1', 'port' => 6379),
                'resquePrefix' => 'resque'
        );

        $this->settings = array_merge($this->settings, $settings);
        $this->settings['resquePrefix'] = $this->settings['resquePrefix'] .':';

        $cube = $this->getMongo()->selectDB($this->settings['mongo']['database']);

        $thisQueues =& $this->queues;
        $this->workers = array_map(
            function ($name) use (&$thisQueues) {
                list($host, $process, $q) = explode(':', $name);
                $q = explode(',', $q);
                array_walk(
                    $q,
                    function ($qu) use (&$thisQueues) {
                        if (isset($thisQueues[$qu])) {
                            $thisQueues[$qu]++;
                        } else {
                            $thisQueues[$qu] = 1;
                        }
                    }
                );
                return array('fullname' => $name, 'host' => $host, 'process' => $process, 'queues' => $q);
            },
            $this->getRedis()->smembers($this->settings['resquePrefix'] . 'workers')
        );

        $redisPipeline = $this->getRedis()->multi(\Redis::PIPELINE);
        foreach ($this->workers as $worker) {
            $redisPipeline
            ->get($this->settings['resquePrefix'] . 'worker:' . $worker['fullname'] . ':started')
            ->get($this->settings['resquePrefix'] . 'stat:processed:' . $worker['fullname'])
            ->get($this->settings['resquePrefix'] . 'stat:failed:' . $worker['fullname']);
        }

        $result = $redisPipeline->exec();
        unset($redisPipeline);

        $this->stats['active'] = array('processed' => 0, 'failed' => 0);

        for ($i = 0, $total = count($result), $j = 0; $i < $total; $i += 3) {
            $this->workers[$j]['start'] = new \DateTime($result[$i]);
            $this->stats['active']['processed'] += $this->workers[$j]['processed'] = (int) $result[$i+1];
            $this->stats['active']['failed'] += $this->workers[$j++]['failed'] = (int) $result[$i+2];

        }

        unset($result);


        $this->stats['total'] = array_combine(
            array('processed', 'failed'),
            array_map(
                function ($s) {
                    return (int) $s;
                },
                $this->getRedis()->multi(\Redis::PIPELINE)
                    ->get($this->settings['resquePrefix'] . 'stat:processed')
                    ->get($this->settings['resquePrefix'] . 'stat:failed')
                    ->exec()
            )
        );

        $this->setupIndexes();
    }


    /**
     * Return a mongo connection instance
     *
     * @since 1.0.0
     */
    private function getMongo()
    {
        if ($this->mongo !== null) {
            return $this->mongo;
        }

        try {
            $this->mongo = new \Mongo($this->settings['mongo']['host'] . ':' . $this->settings['mongo']['port']);
            return $this->mongo;
        } catch (\MongoConnectionException $e) {
            throw new DatabaseConnectionException('Could not connect to Mongo Server');
        }
    }


    /**
     * Return a redis connection instance
     *
     * @since 1.0.0
     */
    private function getRedis()
    {
        if ($this->redis !== null) {
            return $this->redis;
        }

        try {
            $this->redis = new \Redis();
            $this->redis->connect($this->settings['redis']['host'], $this->settings['redis']['port']);
            return $this->redis;
        } catch (\RedisException $e) {
            throw new DatabaseConnectionException('Could not connect to Redis Server');
        }
    }


    /**
     * Return general stats about active workers
     *
     * @since 1.0.0
     * @return array:
     */
    public function getStats()
    {
        return $this->stats;
    }


    /**
     * Return active workers stats
     *
     * @since 1.0.0
     * @return multitype:
     */
    public function getWorkers()
    {
        return $this->workers;
    }


    /**
     * Return list of queues
     *
     * @since 1.0.0
     */
    public function getQueues()
    {
        return $this->queues;
    }


    /**
     * Return jobs filtered by conditions specified in $options
     *
     * @since 1.1.0
     */
    public function getJobs($options = array())
    {
        $cube = $this->getMongo()->selectDB($this->settings['mongo']['database']);
        $jobsCollection = $cube->selectCollection('got_events');

        $default = array(
                'workerId' => null,
                'jobId' => null,
                'page' => 1,
                'limit' => null,
                'sort' => array('t' => -1),
                'status' => null,
                'type' => 'find',
                'date_after' => null,
                'date_before' => null,
                'class' => null,
                'queue' => null,
                'worker' => array(),
                'format' => true
            );

        $options = array_merge($default, $options);

        $conditions = array();

        if (!empty($options['jobId'])) {
            if (!is_array($options['jobId'])) {
                $options['jobId'] = array($options['jobId']);
            }
            $conditions['d.args.payload.id'] = array('$in' => $options['jobId']);
        } else {
            if ($options['workerId'] !== null) {
                $conditions['d.worker'] = $options['workerId'];
            }

            if (!empty($options['class'])) {
                $conditions['d.args.payload.class'] = array('$in' => array_map('trim', explode(',', $options['class'])));
            }

            if (!empty($options['queue'])) {
                $conditions['d.args.queue'] = array('$in' => array_map('trim', explode(',', $options['queue'])));
            }

            if (!empty($options['worker'])) {
                if (in_array('old', $options['worker'])) {
                    $workers = array_map(
                        function ($a) {
                            return $a['host'].':'.$a['process'];
                        },
                        $this->   getWorkers()
                    );
                    $exclude = array_diff($workers, $options['worker']);

                    if (!empty($exclude)) {
                        $conditions['d.worker'] = array('$nin' => $exclude);
                    }
                } else {
                    $conditions['d.worker'] = array('$in' => $options['worker']);
                }
            }

            if ($options['status'] === self::JOB_STATUS_FAILED) {
                $cursor = $cube->selectCollection('fail_events')
                    ->find(array(), array('d.job_id'))
                    ->sort($options['sort'])
                    ->limit($options['limit']);
                $ids = array();
                foreach ($cursor as $c) {
                    $ids[] = $c['d']['job_id'];
                }
                $conditions['d.args.payload.id'] = array('$in' => $ids);
            }
        }

        if (!empty($options['date_after'])) {
            $conditions['t']['$gte'] = new \MongoDate(strtotime($options['date_after']));
        }

        if (!empty($options['date_before'])) {
            $conditions['t']['$lt'] = new \MongoDate(strtotime($options['date_before']));
        }

        $jobsCursor = $jobsCollection->find($conditions);
        $jobsCursor->sort($options['sort']);

        if (!empty($options['page']) && !empty($options['limit'])) {
            $jobsCursor->skip(($options['page']-1) * $options['limit'])->limit($options['limit']);
        }

        if ($options['type'] == 'count') {
            return $jobsCursor->count();
        }

        $results = $this->formatJobs($jobsCursor);
        if ($options['format']) {
            return $this->setJobStatus($results);
        }

        return $results;
    }

    /**
     * Return the distribution of jobs by classes
     *
     * @since 1.1.0
     * @param int $limit Number of results to return, null to return all results
     */
    public function getJobsRepartionStats($limit = 10)
    {
        $cube = $this->getMongo()->selectDB($this->settings['mongo']['database']);
        $mapReduceStats = new \MongoCollection($cube, 'map_reduce_stats');
        $startDate = $mapReduceStats->findOne(array('_id' => 'job_stats'), array('date'));
        if (!isset($startDate['date']) || empty($startDate['date'])) {
            $startDate = null;
        } else {
            $startDate = $startDate['date'];
        }

        $stopDate = new \MongoDate();

        $stats = new \stdClass();

        // Computing total jobs distribution stats
        $map = new \MongoCode("function() {emit(this.d.args.payload.class, 1); }");
        $reduce = new \MongoCode(
            "function(key, val) {".
            "var sum = 0;".
            "for (var i in val) {".
            "sum += val[i];".
            "}".
            "return sum;".
            "}"
        );

        $conditions = array('$lt' => $stopDate);
        if ($startDate != null) {
            $conditions['$gte'] = $startDate;
        }
        $cube->command(
            array(
                'mapreduce' => 'got_events',
                'map' => $map,
                'reduce' => $reduce,
                'query' => array('t' => $conditions),
                'out' => array('merge' => 'jobs_repartition_stats')
            )
        );

        $cursor = $cube->selectCollection('jobs_repartition_stats')->find()->sort(array('value' => -1))->limit($limit);

        $stats->total = $cube->selectCollection('got_events')->find()->count();
        foreach ($cursor as $c) {
            $c['percentage'] = round($c['value'] / $stats->total * 100, 2);
            $stats->stats[] = $c;
        }

        $mapReduceStats->update(
            array('_id' => 'job_stats'),
            array('$set' => array('date' => $stopDate)),
            array('upsert' => true)
        );

        return $stats;
    }


    /**
     * Get general jobs statistics, by status
     *
     * @since 1.1.0
     */
    public function getJobsStats()
    {
        $cube = $this->getMongo()->selectDB($this->settings['mongo']['database']);

        $stats = new \stdClass();
        $stats->total = $cube->selectCollection('got_events')->find()->count();
        $stats->count[self::JOB_STATUS_COMPLETE] = $cube->selectCollection('done_events')->find()->count();
        $stats->count[self::JOB_STATUS_FAILED] = $cube->selectCollection('fail_events')->find()->count();
        $stats->perc[self::JOB_STATUS_FAILED] =
            round($stats->count[self::JOB_STATUS_FAILED] / $stats->count[self::JOB_STATUS_COMPLETE] * 100, 2);
        $stats->count[self::JOB_STATUS_WAITING] = 0;

        $queues = $this->getQueues();
        foreach ($queues as $queue => $val) {
            $stats->count[self::JOB_STATUS_WAITING] +=
                $this->getRedis()->llen($this->settings['resquePrefix'] . 'queue:' . $queue);
        }

        $stats->count[self::JOB_STATUS_RUNNING] = 0; // TODO
        $stats->total_active = $this->getRedis()->get($this->settings['resquePrefix'] . 'stat:processed');

        $cursors = $cube->selectCollection('got_events')->find(array(), array('t'))->sort(array('t' => 1))->limit(1);
        foreach ($cursors as $cursor) {
            $stats->oldest = new \DateTime('@'.$cursor['t']->sec);
        }

        $cursors = $cube->selectCollection('got_events')->find(array(), array('t'))->sort(array('t' => -1))->limit(1);
        foreach ($cursors as $cursor) {
            $stats->newest = new \DateTime('@'.$cursor['t']->sec);
        }

        return $stats;
    }


    /**
     * Convert jobs document from MongoDB to a formatted array
     *
     * @param $MongoCursor A MongoCursor from a find() action
     * @return a array of jobs
     */
    private function formatJobs($cursor)
    {
        $jobs = array();
        foreach ($cursor as $doc) {
            $jobs[$doc['d']['args']['payload']['id']] = array(
                            'time' => date('c', $doc['t']->sec),
                            'queue' => $doc['d']['args']['queue'],
                            'worker' => $doc['d']['worker'],
                            'level' => $doc['d']['level'],
                            'class' => $doc['d']['args']['payload']['class'],
                            'args' => var_export($doc['d']['args']['payload']['args'][0], true),
                            'job_id' => $doc['d']['args']['payload']['id']
            );
        }

        return $jobs;
    }


    /**
     * Assign a status to each jobs
     *
     * @since 1.0.0
     * @param array $jobs An array of jobs
     * @return An array of jobs
     */
    private function setJobStatus($jobs)
    {
        $jobIds = array_keys($jobs);

        $cube = $this->getMongo()->selectDB($this->settings['mongo']['database']);


        $jobsCursor = $cube->selectCollection('done_events')->find(array('d.job_id' => array('$in' => $jobIds)));
        foreach ($jobsCursor as $successJob) {
            $jobs[$successJob['d']['job_id']]['status'] = self::JOB_STATUS_COMPLETE;
            unset($jobIds[array_search($successJob['d']['job_id'], $jobIds)]);
        }

        if (!empty($jobIds)) {
            $redisPipeline = $this->getRedis()->multi(\Redis::PIPELINE);

            $jobsCursor = $cube->selectCollection('fail_events')->find(array('d.job_id' => array('$in' => $jobIds)));
            foreach ($jobsCursor as $failedJob) {
                $jobs[$failedJob['d']['job_id']]['status'] = self::JOB_STATUS_FAILED;
                $jobs[$failedJob['d']['job_id']]['log'] = $failedJob['d']['log'];
                $redisPipeline->get($this->settings['resquePrefix'] . 'failed:' . $failedJob['d']['job_id']);
                unset($jobIds[array_search($failedJob['d']['job_id'], $jobIds)]);
            }

            $failedTrace = array_filter($redisPipeline->exec());

            foreach ($failedTrace as $trace) {
                $trace = function_exists('igbinary_unserialize') ? igbinary_unserialize($trace) : unserialize($trace);
                $jobs[$trace['payload']['id']]['trace'] = var_export($trace, true);
            }

        }

        if (!empty($jobIds)) {
            $jobsCursor = $cube->selectCollection('process_events')->find(array('d.job_id' => array('$in' => $jobIds)));
            foreach ($jobsCursor as $processJob) {
                $jobs[$processJob['d']['job_id']]['status'] = self::JOB_STATUS_RUNNING;
                unset($jobIds[array_search($processJob['d']['job_id'], $jobIds)]);
            }
        }
        if (!empty($jobIds)) {
            foreach ($jobIds as $id) {
                $jobs[$id]['status'] = self::JOB_STATUS_WAITING;
            }
        }

        return array_values($jobs);
    }

    /**
     * Create Mongo Collection index
     *
     * @since 1.1.0
     */
    private function setupIndexes()
    {
        $cube = $this->getMongo()->selectDB($this->settings['mongo']['database']);
        $cube->selectCollection('got_events')->ensureIndex('d.args.queue');
        $cube->selectCollection('got_events')->ensureIndex('d.args.payload.class');
        $cube->selectCollection('got_events')->ensureIndex('d.worker');
        $cube->selectCollection('fail_events')->ensureIndex('d.job_id');
        $cube->selectCollection('done_events')->ensureIndex('d.job_id');
        $cube->selectCollection('shutdown_events')->ensureIndex('d.worker');
        $cube->selectCollection('start_events')->ensureIndex('d.worker');
    }
}


/**
 * DatabaseConnectionException class
 *
 * Type of exception thrown when ResqueStat can not connect
 * to a database
 *
 * @subpackage      resqueboard.lib
 * @since            1.0.0
 * @author           Wan Qi Chen <kami@kamisama.me>
 */
class DatabaseConnectionException extends \Exception
{
}

