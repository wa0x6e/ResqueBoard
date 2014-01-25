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
 * @package    ResqueBoard
 * @subpackage ResqueBoard.Lib
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @copyright  2012-2013 Wan Qi Chen
 * @link       http://resqueboard.kamisama.me
 * @since      1.0.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\Lib;

use ResqueBoard\Lib\Service\Service;

/**
 * ResqueStat class
 *
 * Connect to the backend to retrieve php-resque datas, or metrics
 *
 * @subpackage ResqueBoard.Lib
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @since      1.0.0
 */
class ResqueStat
{
    protected $stats = array();
    protected $workers = array();
    protected $schedulerWorkers = array();
    protected $queues = array();

    const JOB_STATUS_WAITING = 1;
    const JOB_STATUS_RUNNING = 2;
    const JOB_STATUS_FAILED = 3;
    const JOB_STATUS_COMPLETE = 4;
    const JOB_STATUS_SCHEDULED = \ResqueScheduler\Job\Status::STATUS_SCHEDULED;

    const CUBE_STEP_10SEC = '1e4';
    const CUBE_STEP_1MIN = '6e4';
    const CUBE_STEP_5MIN = '3e5';
    const CUBE_STEP_1HOUR = '36e5';
    const CUBE_STEP_1DAY = '864e5';

    protected $settings;

    /**
     * @since 1.0.0
     */
    public function __construct($settings = array())
    {

    }

    /**
     * Return count of each jobs status
     *
     * @param string $type Filter stats by this job type
     *
     * @since 1.0.0
     * @return array indexed by job status if more than one job status requested, else int
     */
    public function getStats($type = null)
    {
        $stats = array();
        $validType = array(
            self::JOB_STATUS_FAILED => 'fail',
            self::JOB_STATUS_COMPLETE => 'done',
            self::JOB_STATUS_SCHEDULED => 'movescheduled'
        );

        if ($type === null) {
            $stats = array_combine(array_keys($validType), array_fill(0, count($validType), 0));
        } elseif (array_key_exists($type, $validType)) {
            $stats[$type] = 0;
        } else {
            return false;
        }

        foreach ($stats as $key => $value) {
            $stats[$key] = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], $validType[$key] . '_events')->count();
        }

        return $type === null ? $stats : $stats[$type];
    }


    /**
     * Return list of active workers with their full stats
     *
     * @since 1.0.0
     * @return array Array of workers
     */
    public function getWorkers()
    {
        $this->workers = array_map(
            function ($name) {
                list($host, $process, $q) = explode(':', $name);
                $q = explode(',', $q);
                return array('fullname' => $name, 'host' => $host, 'process' => $process, 'queues' => $q);
            },
            Service::Redis()->smembers('workers')
        );

        $pipelineCommands = array();
        foreach ($this->workers as $worker) {
            $pipelineCommands[] = array('get', 'worker:' . $worker['fullname'] . ':started');
            $pipelineCommands[] = array('get', 'stat:processed:' . $worker['fullname']);
            $pipelineCommands[] = array('get', 'stat:failed:' . $worker['fullname']);
        }

        $result = Service::Redis()->pipeline($pipelineCommands);
        unset($redisPipeline);

        for ($i = 0, $total = count($result), $j = 0; $i < $total; $i += 3) {
            $this->workers[$j]['start'] = new \DateTime($result[$i]);
            $this->workers[$j]['processed'] = (int) $result[$i+1];
            $this->workers[$j++]['failed'] = (int) $result[$i+2];
        }

        return $this->workers;
    }

    /**
     * Return active workers stats
     *
     * @since 1.0.0
     * @return multitype:
     */
    public function getSchedulerWorkers()
    {
        return $this->schedulerWorkers;
    }


    /**
     * Return a worker stats
     *
     * @param int $workerId Process ID of the worker
     *
     * @since 1.2.0
     */
    public function getWorker($workerId)
    {

        $collection = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'start_events');
        $workerStatsMongo = $collection->findOne(array('d.worker' => $workerId), array('d.queues'));

        $workerFullName = $workerId . ':' . implode(',', $workerStatsMongo['d']['queues']);
        list($host, $process) = explode(':', $workerId, 2);

        return array(
            'fullname' => $workerFullName,
            'host' => $host,
            'process' => $process,
            'queues' => $workerStatsMongo['d']['queues'],
            'start' => new \DateTime(Service::Redis()->get('worker:' . $workerFullName . ':started')),
            'processed' => (int)Service::Redis()->get('stat:processed:' . $workerFullName),
            'failed' => (int)Service::Redis()->get('stat:failed:' . $workerFullName)
        );
    }


    /**
     * Return list of all known queues
     *
     * Return the list of queues currently polled by at least one queue
     *
     * @since 1.4.0
     * @return array Array of queues name
     */
    public function getAllQueues()
    {
        return Service::Redis()->smembers('queues');
    }

    /**
     * Return list of all queues
     *
     * Return list of all queues, along with their stats
     *
     * @param array $fields To filters queues search
     * @param array $queues Get infos on these queues
     *
     * @since 1.4.0
     * @return array Array of queues name with their stats
     */
    public function getQueues($fields = array(), $queues = array())
    {

        $queuesCollection = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'got_events');

        if (empty($queues)) {
            $queues = $this->getAllQueues();
        }

        $r = array();
        foreach ($queues as $name) {
            $r[$name] = array(
                'name' => $name,
                'stats' => array(
                    'totaljobs' => 0,
                    'pendingjobs' => 0,
                    'workerscount' => 0
                )
            );
        }

        if (in_array('totaljobs', $fields)) {
            $lastRefresh = Service::Mongo()
                ->selectCollection(Service::$settings['Mongo']['database'], 'map_reduce_stats')
                ->findOne(array('_id' => 'queue_stats'), array("date", "stats"));

            $now = new \MongoDate();

            $conditions = array();

            if (isset($lastRefresh['date'])) {
                $conditions['t'] = array('$gt' => $now);
            }

            $queues = $queuesCollection->distinct('d.args.queue', $conditions);

            $results = isset($lastRefresh['stats']) ? $lastRefresh['stats'] : array();
            foreach ($queues as $q) {
                $conditions['d.args.queue'] = $q;
                $results[$q] = $queuesCollection->count($conditions) + (isset($results[$q]) ? $results[$q] : 0);
            }

            Service::Mongo()
                ->selectCollection(Service::$settings['Mongo']['database'], 'map_reduce_stats')
                ->save(
                    array(
                        '_id' => 'queue_stats',
                        'date' => $now,
                        'stats' => $results
                    )
                );

            // Format results for the API
            foreach ($results as $name => $count) {
                if (!isset($r[$name])) {
                    $r[$name] = array(
                        'name' => $name,
                        'stats' => array(
                            'totaljobs' => $count,
                            'pendingjobs' => 0,
                            'workerscount' => 0
                        )
                    );
                } else {
                    $r[$name]['stats']['totaljobs'] = $count;
                }

            }
        }

        if (in_array('pendingjobs', $fields)) {
            $pipelineCommands = array();
            foreach ($r as $name => $stats) {
                $pipelineCommands[] = array('llen', 'queue:' . $name);
            }

            $count = Service::Redis()->pipeline($pipelineCommands);
            $i = 0;
            foreach ($r as $name => $stats) {
                $r[$name]['stats']['pendingjobs'] = $count[$i];
                $i++;
            }

        }

        if (in_array('workerscount', $fields)) {
            $workers = $this->getWorkers();

            foreach ($workers as $w) {
                foreach ($w['queues'] as $q) {
                    if (isset($r[$q])) {
                        $r[$q]['stats']['workerscount']++;
                    } else {
                        $r[$q] = array(
                            'name' => $q,
                            'stats' => array(
                                'totaljobs' => 0,
                                'pendingjobs' => 0,
                                'workerscount' => 1
                            )
                        );
                    }
                }
            }
        }

        return $r;
    }


    /**
     * Return jobs filtered by conditions specified in $options
     *
     * @param array $options To filter jobs search
     *
     * @since 1.1.0
     * @return array An array of jobs
     */
    public function getJobs($options = array())
    {

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

        if ($options['date_before'] !== null && !is_int($options['date_before'])) {
            $options['date_before'] = strtotime($options['date_before']);
        }

        if ($options['date_after'] !== null && !is_int($options['date_after'])) {
            $options['date_after'] = strtotime($options['date_after']);
        }

        if (isset($options['status']) && $options['status'] === self::JOB_STATUS_WAITING) {
            return $this->getPendingJobs($options);
        }


        $jobsCollection = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'got_events');

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
                        $this->getWorkers()
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
                $cursor = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'fail_events')
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
            $conditions['t']['$gte'] = new \MongoDate($options['date_after']);
        }

        if (!empty($options['date_before'])) {
            $conditions['t']['$lte'] = new \MongoDate($options['date_before']);
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
     * Get the number of jobs at a specific time
     *
     * @param array $options To filter the search
     *
     * @since 2.0.0
     * @return  Array
     */
    public function getJobsCount($options = array())
    {

        $default = array(
                'workerId' => null,
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

        if ($options['date_before'] !== null && !is_int($options['date_before'])) {
            $options['date_before'] = strtotime($options['date_before']);
        }

        if ($options['date_after'] !== null && !is_int($options['date_after'])) {
            $options['date_after'] = strtotime($options['date_after']);
        }




        $conditions = array();

        if (!empty($options['date_after'])) {
            $conditions['t']['$gte'] = new \MongoDate($options['date_after']);
        }

        if (!empty($options['date_before'])) {
            $conditions['t']['$lt'] = new \MongoDate($options['date_before']);
        }

        $results = array();

        $jobsDoneCollection = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'done_events');
        $jobsFailCollection = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'fail_events');

        $jobsDoneCursor = $jobsDoneCollection->find($conditions, array('t' => true));
        foreach ($jobsDoneCursor as $job) {
            if (isset($results[$job['t']->sec])) {
                $results[$job['t']->sec] += 1;
            } else {
                $results[$job['t']->sec] = 1;
            }
        }

        $jobsFailCursor = $jobsFailCollection->find($conditions, array('t' => true));
        foreach ($jobsFailCursor as $job) {
            if (isset($results[$job['t']->sec])) {
                $results[$job['t']->sec] += 1;
            } else {
                $results[$job['t']->sec] = 1;
            }
        }

        return $results;
    }

    /**
     * Return a list of pending jobs
     *
     * @since 1.4.0
     * @return Array an Array of jobs
     */
    protected function getPendingJobs($options)
    {

        $queuesList = empty($options['queue']) ? $this->getAllQueues() : array($options['queue']);
        $jobs = array();
        $queues = array();
        foreach ($queuesList as $queueName) {
            $keyName = 'queue:' . $queueName;
            $limit = $options['limit'] === null ? Service::Redis()->llen($keyName)-1 : $options['limit'];
            $queues[$queueName] = Service::Redis()->lrange($keyName, 0, $limit);
        }


        foreach ($queues as $queue => $jobs) {
            for ($i = count($jobs)-1; $i >= 0; $i--) {
                $jobs[$i] = json_decode($jobs[$i], true);
                $jobs[$i] = array(
                    'd' => array(
                        'args' => array(
                            'queue' => $queue,
                            'payload' => array(
                                'class' => $jobs[$i]['class'],
                                'id' => $jobs[$i]['id'],
                                'args' => $jobs[$i]['args']
                                )
                            )
                        )
                    );
            }
        }

        $jobs = $this->formatJobs($jobs);
        array_walk(
            $jobs,
            function (&$j) {
                $j['status'] = \ResqueBoard\Lib\ResqueStat::JOB_STATUS_WAITING;
            }
        );
        return $jobs;
    }

    /**
     * Return the number of pending jobs in a queue
     *
     * @param String $queue Name of the queue, or null to get the count of pending jobs from all queues
     *
     * @return array Queue name indexed array of jobs count
     */
    public function getPendingJobsCount($queue = null)
    {
        $queuesList = $queue === null ? $this->getAllQueues() : $queue;
        if (!is_array($queuesList)) {
            $queuesList = array($queuesList);
        }

        $pipelineCommands = array();
        foreach ($queuesList as $queueName) {
            $pipelineCommands[] = array('llen', 'queue:' . $queueName);
        }

        return array_combine($queuesList, Service::Redis()->pipeline($pipelineCommands));
    }




    /**
     * Return logs filtered by conditions specified in $options
     *
     * @param array $options To filter the search
     *
     * @since 1.2.0
     * @return array Array of logs entries
     */
    public function getLogs($options = array())
    {
        $eventTypeList = array('check' ,'done', 'fail', 'fork', 'found', 'got', 'kill', 'process', 'prune', 'reconnect', 'shutdown', 'sleep', 'start');

        $default = array(
            'page' => 1,
            'limit' => null,
            'sort' => array('t' => 1),
            'event_level' => array(),
            'event_type' => '',
            'date_after' => null,
            'date_before' => null,
            'type' => 'find'
        );


        $options = array_merge($default, $options);


        if ($options['date_before'] !== null && !is_int($options['date_before'])) {
            $options['date_before'] = strtotime($options['date_before']);
        }

        if ($options['date_after'] !== null && !is_int($options['date_after'])) {
            $options['date_after'] = strtotime($options['date_after']);
        }

        $conditions = array();


        if (!empty($options['event_level'])) {
            $conditions['d.level'] = array(
                '$in' => array_map(
                    function ($level) {
                        return (int)$level;
                    },
                    $options['event_level']
                )
            );
        }

        if (!empty($options['date_after'])) {
            $conditions['t']['$gte'] = new \MongoDate($options['date_after']);
        }

        if (!empty($options['date_before'])) {
            $conditions['t']['$lt'] = new \MongoDate($options['date_before']);
        }

        $results = array();

        $jobsCollection = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], $options['event_type'] . '_events');

        $jobsCursor = $jobsCollection->find($conditions);
        $jobsCursor->sort($options['sort']);

        if (!empty($options['page']) && !empty($options['limit'])) {
            $jobsCursor->skip(($options['page']-1) * $options['limit'])->limit($options['limit']);
        }

        if ($options['type'] == 'count') {
            return $jobsCursor->count();
        }

        foreach ($jobsCursor as $cursor) {
            $temp = array();

            $temp['date'] = new \DateTime('@' . $cursor['t']->sec);

            if (isset($cursor['d']['worker'])) {
                $temp['worker'] = $cursor['d']['worker'];
            }

            if (isset($cursor['d']['level'])) {
                $temp['level'] = $cursor['d']['level'];
            }

            if (isset($cursor['d']['job_id'])) {
                $temp['job_id'] = $cursor['d']['job_id'];
            } else if (isset($cursor['d']['args']['payload']['id'])) {
                $temp['job_id'] = $cursor['d']['args']['payload']['id'];
            }

            $temp['event_type'] = $options['event_type'];

            $results[] = $temp;
        }


        usort(
            $results,
            function (
                $a,
                $b
            ) {
                if ($a['date'] == $b['date']) {
                    return 0;
                }
                return ($a['date'] < $b['date']) ? -1 : 1;
            }
        );

        return $results;
    }

    /**
     * Get the number of jobs processed for a period of time
     *
     * Get the number of jobs processed between a $start and an $end date,
     * divided into $step interval
     *
     * @param DateTime $start Start date
     * @param DateTime $end   End date
     * @param const    $step  Cube constant for step
     *
     * @throws \Exception       If curl extension is not installed
     * @throws \Exception       If unable to init the curl session
     * @throws \Exception       If cube does not return a valid response
     * @since  1.3.0
     * @return array
     */
    public function getJobsMatrix($start, $end, $step)
    {
        return Service::Cube()->getMetric(
            'sum(got)&start=' . urlencode($start->format('Y-m-d\TH:i:sO')) .
            '&stop=' . urlencode($end->format('Y-m-d\TH:i:sO')) . '&step=36e5'
        );
    }

    /**
     * Return the distribution of jobs by classes
     *
     * @param int $limit Number of results to return, null to return all results
     *
     * @since 1.1.0
     * @return array
     */
    public function getJobsRepartionStats($limit = 10)
    {
        $mapReduceStats = new \MongoCollection(Service::Mongo()->selectDB(Service::$settings['Mongo']['database']), 'map_reduce_stats');
        $startDate = $mapReduceStats->findOne(array('_id' => 'job_stats'), array('date'));
        if (!isset($startDate['date']) || empty($startDate['date'])) {
            $startDate = null;
        } else {
            $startDate = $startDate['date'];
        }

        $stopDate = new \MongoDate();

        $mapReduceStats->update(
            array('_id' => 'job_stats'),
            array('$set' => array('date' => $stopDate)),
            array('upsert' => true)
        );

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
        Service::Mongo()->selectDB(Service::$settings['Mongo']['database'])->command(
            array(
                'mapreduce' => 'got_events',
                'map' => $map,
                'reduce' => $reduce,
                'query' => array('t' => $conditions),
                'out' => array('merge' => 'jobs_repartition_stats')
            )
        );

        $cursor = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'jobs_repartition_stats')->find()->sort(array('value' => -1))->limit($limit);

        $stats->total = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'got_events')->find()->count();
        $stats->stats = array();
        foreach ($cursor as $c) {
            $c['percentage'] = round($c['value'] / $stats->total * 100, 2);
            $stats->stats[] = $c;
        }

        return $stats;
    }


    /**
     * Get general jobs statistics, by status
     *
     * @param array $options To filter the search
     *
     * @since 1.1.0
     * @return array An array of jobs count, by status
     */
    public function getJobsStats($options = array())
    {


        $filter = array();

        if (isset($options['start'])) {
            $filter['t']['$gte'] = new \MongoDate(strtotime($options['start']));
        }

        if (isset($options['end'])) {
            $filter['t']['$lt'] = new \MongoDate(strtotime($options['end']));
        }

        $stats = new \stdClass();

        if (in_array('total', $options['fields'])) {
            $stats->total = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'got_events')->find($filter)->count();
        }

        if (in_array(self::JOB_STATUS_COMPLETE, $options['fields'])) {
            $stats->count[self::JOB_STATUS_COMPLETE] = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'done_events')->find($filter)->count();
        }

        if (in_array(self::JOB_STATUS_FAILED, $options['fields'])) {
            $stats->count[self::JOB_STATUS_FAILED] = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'fail_events')->find($filter)->count();
            $stats->perc[self::JOB_STATUS_FAILED] =
                ($stats->total == 0)
                    ? 0
                    : round($stats->count[self::JOB_STATUS_FAILED] / $stats->total * 100, 2);
        }

        $stats->count[self::JOB_STATUS_WAITING] = 0;

        if (in_array(self::JOB_STATUS_SCHEDULED, $options['fields'])) {
            $stats->count[self::JOB_STATUS_SCHEDULED] = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'movescheduled_events')->find($filter)->count();
            $stats->perc[self::JOB_STATUS_SCHEDULED] =
                ($stats->total == 0)
                    ? 0
                    : round($stats->count[self::JOB_STATUS_SCHEDULED] / $stats->total * 100, 2);
        }

        if (in_array(self::JOB_STATUS_WAITING, $options['fields'])) {
            $queues = $this->getAllQueues();
            $pipelineCommands = array();
            foreach ($queues as $queueName) {
                $pipelineCommands[] = array('llen', 'queue:' . $queueName);
            }
            $stats->count[self::JOB_STATUS_WAITING] = array_sum(Service::Redis()->pipeline($pipelineCommands));
        }

        $stats->count[self::JOB_STATUS_RUNNING] = 0; // TODO

        $stats->oldest = null;
        $stats->newest = null;

        if (in_array('oldest', $options['fields'])) {
            $cursors = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'got_events')->find(array(), array('t'))->sort(array('t' => 1))->limit(1);
            foreach ($cursors as $cursor) {
                $stats->oldest = new \DateTime('@'.$cursor['t']->sec);
            }
        }

        if (in_array('newest', $options['fields'])) {
            $cursors = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'got_events')->find(array(), array('t'))->sort(array('t' => -1))->limit(1);
            foreach ($cursors as $cursor) {
                $stats->newest = new \DateTime('@'.$cursor['t']->sec);
            }
        }


        return $stats;
    }


    /**
     * Convert jobs document from MongoDB or Redis entry to a formatted array
     *
     * @param \MongoCursor $cursor A traversable object
     *
     * @return an array of jobs
     */
    private function formatJobs($cursor)
    {
        $jobs = array();
        foreach ($cursor as $doc) {
            $jobs[$doc['d']['args']['payload']['id']] = array(
                            'time' => isset($doc['t']) ? date('c', $doc['t']->sec) : null,
                            'queue' => $doc['d']['args']['queue'],
                            'worker' => isset($doc['d']['worker']) ? $doc['d']['worker'] : null,
                            'level' => isset($doc['d']['level']) ? $doc['d']['level'] : null,
                            'class' => $doc['d']['args']['payload']['class'],
                            'args' => var_export($doc['d']['args']['payload']['args'][0], true),
                            'job_id' => $doc['d']['args']['payload']['id']

            );


            $jobs[$doc['d']['args']['payload']['id']]['took'] = isset($doc['d']['time']) ? $doc['d']['time'] : null;

        }

        return $jobs;
    }


    /**
     * Assign a status to each jobs
     *
     * @param array $jobs An array of jobs
     *
     * @since 1.0.0
     * @return An array of jobs
     */
    private function setJobStatus($jobs)
    {
        $jobIds = array_keys($jobs);




        $jobsCursor = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'done_events')->find(array('d.job_id' => array('$in' => $jobIds)));
        foreach ($jobsCursor as $successJob) {
            $jobs[$successJob['d']['job_id']]['status'] = self::JOB_STATUS_COMPLETE;
            $jobs[$successJob['d']['job_id']]['took'] = $successJob['d']['time'];
            unset($jobIds[array_search($successJob['d']['job_id'], $jobIds)]);
        }

        if (!empty($jobIds)) {

            $jobsCursor = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'fail_events')->find(array('d.job_id' => array('$in' => $jobIds)));
            $pipelineCommands = array();
            foreach ($jobsCursor as $failedJob) {
                $jobs[$failedJob['d']['job_id']]['status'] = self::JOB_STATUS_FAILED;
                $jobs[$failedJob['d']['job_id']]['log'] = $failedJob['d']['log'];
                $jobs[$failedJob['d']['job_id']]['took'] = $failedJob['d']['time'];
                $pipelineCommands[] = array('get', 'failed:' . $failedJob['d']['job_id']);
                unset($jobIds[array_search($failedJob['d']['job_id'], $jobIds)]);
            }

            $failedTrace = array_filter(Service::Redis()->pipeline($pipelineCommands));

            foreach ($failedTrace as $trace) {
                $trace = unserialize($trace);
                $jobs[$trace['payload']['id']]['trace'] = var_export($trace, true);
            }

        }

        if (!empty($jobIds)) {
            $jobsCursor = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'process_events')->find(array('d.job_id' => array('$in' => $jobIds)));
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

    public function getCubeMetric($options)
    {
        return Service::Cube()->getMetric(
            $options['expression'] .
            '&start=' . urlencode($options['start']->format('c')) .
            (isset($options['end']) ? ('&stop=' . urlencode($options['end']->format('c'))) : '') .
            (isset($options['step']) ? ('&step=' . $options['step']) : '') .
            (isset($options['limit']) ? ('&limit=' . $options['limit']) : '')
        );
    }

    /**
     * Create Mongo Collection index
     *
     * @since 1.1.0
     * @return void
     */
    private function setupIndexes()
    {

        Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'got_events')->ensureIndex('d.args.queue');
        Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'got_events')->ensureIndex('d.args.payload.class');
        Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'got_events')->ensureIndex('d.worker');
        Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'fail_events')->ensureIndex('d.job_id');
        Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'done_events')->ensureIndex('d.job_id');
        Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'shutdown_events')->ensureIndex('d.worker');
        Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], 'start_events')->ensureIndex('d.worker');
    }
}
