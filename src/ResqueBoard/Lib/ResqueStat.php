<?php

namespace ResqueBoard\Lib;

class ResqueStat
{
    protected $stats = array();
    protected $workers = array();
    protected $queues = array();
    
    private $settings;
    private $redis;
    private $mongo;
    
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
        $this->workers = array_map(function($name) use (&$thisQueues){
            list($host, $process, $q) = explode(':', $name);
            $q = explode(',', $q);
            array_walk($q, function($qu)use(&$thisQueues){
                if (isset($thisQueues[$qu])) {
                    $thisQueues[$qu]++;
                } else {
                    $thisQueues[$qu] = 1;
                }
            });
            return array('fullname' => $name, 'host' => $host, 'process' => $process, 'queues' => $q);
        }, $this->getRedis()->smembers($this->settings['resquePrefix'] . 'workers'));
        
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
                        function($s){return (int) $s; },
                        $this->getRedis()->multi(\Redis::PIPELINE)
                            ->get($this->settings['resquePrefix'] . 'stat:processed')
                            ->get($this->settings['resquePrefix'] . 'stat:failed')
                            ->exec()
                        )
                );
    }
    
    
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
    
    public function getStats()
    {
        return $this->stats;
    }
    
    public function getWorkers()
    {
        return $this->workers;
    }
    
    public function getQueues()
    {
        return $this->queues;
    }
    
    
    /**
     * Return a list of jobs that was processed between
     * a $start and an $end date
     */
    public function getJobs($start, $end)
    {
        $cube = $this->getMongo()->selectDB($this->settings['mongo']['database']);
        $jobsCollection = $cube->selectCollection('got_events');
        
        $jobsCursor = $jobsCollection->find(array('t' => array('$gte' => new \MongoDate($start), '$lt' => new \MongoDate($end))));
        $jobsCursor->sort(array('d.worker' => 1));
        
        $jobs = array();
        foreach ($jobsCursor as $doc) {
            $jobs[] = array(
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
}

class DatabaseConnectionException extends \Exception {}