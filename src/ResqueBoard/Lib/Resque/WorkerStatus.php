<?php
/**
 * WorkerStatus class
 *
 * Manage Resque workers state
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
 * @subpackage    resqueboard.lib.resque
 * @since         1.2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\Lib\Resque;

/**
 * WorkerStatus Class
 *
 * Manage Resque workers state
 *
 * @author Wan Qi Chen <kami@kamisama.me>
 */
class WorkerStatus
{
    /**
     * Save the workers arguments
     *
     * Used when restarting the worker
     */
    public function addWorker($args)
    {
        \Resque::Redis()->rpush('ResqueWorker', serialize($args));
    }

    /**
     * Register a Scheduler Worker
     *
     * @since  2.3.0
     * @return boolean True if a Scheduler worker is found among the list of active workers
     */
    public function registerSchedulerWorker()
    {
        $workers = Resque_Worker::all();
        foreach ($workers as $worker) {
            if (array_pop(explode(':', $worker)) === ResqueScheduler\ResqueScheduler::QUEUE_NAME) {
                \Resque::Redis()->set('ResqueSchedulerWorker', (string)$worker);
                return true;
            }
        }
        return false;
    }

    /**
     * Test if a given worker is a scheduler worker
     *
     * @param   Worker|string   $worker Worker to test
     * @since   2.3.0
     * @return  boolean True if the worker is a scheduler worker
     */
    public function isSchedulerWorker($worker)
    {
        return array_pop(explode(':', (string)$worker)) === ResqueScheduler\ResqueScheduler::QUEUE_NAME;
    }

    /**
     * Check if the Scheduler Worker is already running
     * @param  boolean $check Check agains list of all active workers, in case the previous scheduler worker was not stopped properly
     * @return boolean        True if the scheduler worker is already running
     */
    public function isRunningSchedulerWorker($check = false)
    {
        if ($check) {
            $this->unregisterSchedulerWorker();
            return $this->registerSchedulerWorker();
        }
        return \Resque::Redis()->exists('ResqueSchedulerWorker');
    }

    /**
     * Unregister a Scheduler Worker
     *
     * @since  2.3.0
     * @return boolean True if the scheduler worker existed and was successfully unregistered
     */
    public function unregisterSchedulerWorker()
    {
        return \Resque::Redis()->del('ResqueSchedulerWorker') > 0;
    }

    /**
     * Return all started workers arguments
     *
     * @return array An array of settings, by worker
     */
    public function getWorkers()
    {
        $listLength = \Resque::Redis()->llen('ResqueWorker');
        $workers = \Resque::Redis()->lrange('ResqueWorker', 0, $listLength - 1);

        if (empty($workers)) {
            return false;
        } else {
            $temp = array();
            foreach ($workers as $worker) {
                $temp[] = unserialize($worker);
            }
            return $temp;
        }
    }

    /**
     * Clear all workers saved arguments
     */
    public function clearWorker()
    {
        \Resque::Redis()->del('ResqueWorker');
        \Resque::Redis()->del('PausedWorker');
    }

    /**
     * Mark a worker as paused
     *
     * @since 2.0.0
     * @param string $workerName Name of the paused worker
     */
    public function setPausedWorker($workerName)
    {
        \Resque::Redis()->sadd('PausedWorker', $workerName);
    }

    /**
     * Mark a worker as active
     *
     * @since 2.0.0
     * @param string $workerName Name of the worker
     */
    public function setActiveWorker($workerName)
    {
        \Resque::Redis()->srem('PausedWorker', $workerName);
    }

    /**
     * Return a list of paused workers
     *
     * @since 2.0.0
     * @return  array of workers name
     */
    public function getPausedWorker()
    {
        return (array)\Resque::Redis()->smembers('PausedWorker');
    }
}
