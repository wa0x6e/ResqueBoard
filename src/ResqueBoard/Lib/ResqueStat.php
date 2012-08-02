<?php

namespace ResqueBoard\Lib;

class ResqueStat
{
    protected $stats = array();
    protected $workers = array();
    protected $queues = array();
    
    private $settings;
    
    public function __construct($settings = array())
    {
        $this->settings = array(
                'mongo' => array('host' => 'localhost', 'port' => 27017, 'database' => 'cube_development'),
                'redis' => array('host' => '127.0.0.1', 'port' => 6379),
                'resquePrefix' => 'resque'
        );
        
        $this->settings = array_merge($this->settings, $settings);
        $this->settings['resquePrefix'] = $this->settings['resquePrefix'] .':';
        
        try {
            $mongo = new \Mongo($this->settings['mongo']['host'] . ':' . $this->settings['mongo']['port']);
            $cube = $mongo->selectDB($this->settings['mongo']['database']);
        } catch (\MongoConnectionException $e) {
            throw new DatabaseConnectionException('Could not connect to Mongo Server');
        }
        
        try {
            $redis = new \Redis();
            $redis->connect($this->settings['redis']['host'], $this->settings['redis']['port']);
        } catch (\RedisException $e) {
            throw new DatabaseConnectionException('Could not connect to Redis Server');
        }

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
        }, $redis->smembers($this->settings['resquePrefix'] . 'workers'));
        
        $redisPipeline = $redis->multi(\Redis::PIPELINE);
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
                        $redis->multi(\Redis::PIPELINE)
                            ->get($this->settings['resquePrefix'] . 'stat:processed')
                            ->get($this->settings['resquePrefix'] . 'stat:failed')
                            ->exec()
                        )
                );
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
}

class DatabaseConnectionException extends \Exception {}