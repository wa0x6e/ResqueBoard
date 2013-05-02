<?php
/**
 * Api class
 *
 * Manage Resque workers
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
 * @subpackage    resqueboard.lib
 * @since         1.2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\Lib\Resque;

/**
 * Api Class
 *
 * Manage Resque workers
 *
 * @author Wan Qi Chen <kami@kamisama.me>
 */
class Api
{
    public function __construct()
    {
        $this->workerStatus = new WorkerStatus();
    }


    /**
     * Stop a worker
     *
     * @throws ResqueBoard\Lib\Resque\InvalidWorkerIdException
     * @param  [type] $worker [description]
     * @return [type]           [description]
     */
    public function stop($worker)
    {
        return $this->sendSignal($this->getProcessId($worker), 'SIGQUIT');
    }


    /**
     * Pause a worker
     *
     * @throws ResqueBoard\Lib\Resque\InvalidWorkerIdException
     * @param  [type] $worker [description]
     * @return [type]           [description]
     */
    public function pause($worker)
    {
        $id = $this->getProcessId($worker);

        /* if (!array_key_exists($worker, $this->workerStatus->getWorkers())) {
            throw new WorkerNotExistException();
        }
        */
        if (in_array($worker, $this->workerStatus->getPausedWorker())) {
            throw new WorkerAlreadyPausedException();
        }

        $res = $this->sendSignal($id, '-USR2');
        if ($res === true) {
            $this->workerStatus->setPausedWorker($worker);
            return true;
        }
        return $res;

    }


    /**
     * Resume a worker
     *
     * @throws ResqueBoard\Lib\Resque\InvalidWorkerIdException
     * @param  [type] $worker [description]
     * @return [type]           [description]
     */
    public function resume($worker)
    {
        if (!in_array($worker, $this->workerStatus->getPausedWorker())) {
            throw new WorkerNotPausedException();
        }

        $res = $this->sendSignal($this->getProcessId($worker), '-CONT');

        if ($res === true) {
            $this->workerStatus->setActiveWorker($worker);
        }
        return $res;
    }


    /**
     * Send a signal to a process
     *
     * @param  [type] $process [description]
     * @param  [type] $signal  [description]
     * @return [type]          [description]
     */
    protected function sendSignal($process, $signal)
    {
        $output = array();
        $message = exec('kill ' . $signal . ' ' . $process . ' 2>&1', $output, $code);
        return $code === 0 ? true : $message;
    }


    /**
     * Return the process number from a worker ID
     *
     * @throws ResqueBoard\Lib\Resque\InvalidWorkerNameException
     * @param  [type] $worker [description]
     * @return [type]           [description]
     */
    protected function getProcessId($worker)
    {
        $tokens = explode(':', $worker);
        if (count($tokens) !== 3 || preg_match('/^\d+$/', $tokens[1]) === 0) {
            throw new InvalidWorkerNameException();
        }
        return $tokens[1];
    }
}
