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
 * @package    ResqueBoard
 * @subpackage ResqueBoard.Test
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @copyright  2012-2013 Wan Qi Chen
 * @link       http://resqueboard.kamisama.me
 * @since      1.2.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\Lib\Resque;

/**
 * Api Class
 *
 * Manage Resque workers
 *
 * @subpackage ResqueBoard.Lib.Resque
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @since      1.2.0
 */
class Api
{
    public $ResqueStatus = null;

    /**
     * Construct the API class
     */
    public function __construct()
    {
        $this->ResqueStatus = new \ResqueStatus\ResqueStatus(\ResqueBoard\Lib\Service\Service::Redis());
    }


    /**
     * Stop a worker
     *
     * @param string $worker The worker name
     *
     * @throws ResqueBoard\Lib\Resque\InvalidWorkerIdException when worker name is not valid
     * @throws ResqueBoard\Lib\Resque\WorkerNotExistException if attempting to pause a non-existent worker
     * @return true|string True if the worker was stopped, else the error message
     */
    public function stop($worker)
    {
        $pid = $this->getProcessId($worker);

        if (!array_key_exists($pid, $this->ResqueStatus->getWorkers())) {
            throw new WorkerNotExistException();
        }

        $this->ResqueStatus->removeWorker($pid);
        return $this->sendSignal($pid, 'QUIT');

    }


    /**
     * Pause a worker
     *
     * @param String $worker Name of the worker to pause
     *
     * @throws ResqueBoard\Lib\Resque\InvalidWorkerIdException when worker name is not valid
     * @throws ResqueBoard\Lib\Resque\WorkerNotExistException when attempting to pause a non-existent worker
     * @throws ResqueBoard\Lib\Resque\WorkerAlreadyPausedException if attempting to pause an already paused worker
     * @return true|string True if the the worker was paused, else the error message
     */
    public function pause($worker)
    {
        $pid = $this->getProcessId($worker);

        if (!array_key_exists($pid, $this->ResqueStatus->getWorkers())) {
            throw new WorkerNotExistException();
        }

        if (in_array($worker, $this->ResqueStatus->getPausedWorker())) {
            throw new WorkerAlreadyPausedException();
        }

        $res = $this->sendSignal($pid, '-USR2');
        if ($res === true) {
            $this->ResqueStatus->setPausedWorker($worker);
        }
        return $res;

    }


    /**
     * Resume a worker
     *
     * @param string $worker Worker name
     *
     * @throws ResqueBoard\Lib\Resque\InvalidWorkerIdException when worker name is not valid
     * @throws ResqueBoard\Lib\Resque\WorkerNotExistException when attempting to pause a non-existent worker
     * @throws ResqueBoard\Lib\Resque\WorkerNotPausedException if attempting to resume a not-paused worker
     * @return true|string True if the worker was resumed, else the error message
     */
    public function resume($worker)
    {
        $pid = $this->getProcessId($worker);

        if (!array_key_exists($pid, $this->ResqueStatus->getWorkers())) {
            throw new WorkerNotExistException();
        }

        if (!in_array($worker, $this->ResqueStatus->getPausedWorker())) {
            throw new WorkerNotPausedException();
        }

        $res = $this->sendSignal($pid, '-CONT');

        if ($res === true) {
            $this->ResqueStatus->setPausedWorker($worker, false);
        }
        return $res;
    }


    /**
     * Send a signal to a process
     *
     * @param string $process Process Id
     * @param string $signal  Signal to send to the process
     *
     * @return [type]          [description]
     */
    protected function sendSignal($process, $signal)
    {
        $output = array();
        $message = exec('/bin/kill ' . $signal . ' ' . $process . ' 2>&1', $output, $code);
        return $code === 0 ? true : $message;
    }


    /**
     * Return the process number from a worker ID
     *
     * @param string $worker Worker name
     *
     * @throws ResqueBoard\Lib\Resque\InvalidWorkerNameException when the worker name is not formatted as host:pid:queue
     * @throws ResqueBoard\Lib\Resque\NotLocalWorkerException when the worker is not running on the current machine
     * @return int Worker process ID
     */
    protected function getProcessId($worker)
    {
        $tokens = explode(':', $worker);
        if (count($tokens) !== 3 || preg_match('/^\d+$/', $tokens[1]) === 0) {
            throw new InvalidWorkerNameException();
        }

        if ($tokens[0] !== (function_exists('gethostname') ? gethostname() : php_uname('n'))) {
            throw new NotLocalWorkerException();
        }
        return $tokens[1];
    }
}
