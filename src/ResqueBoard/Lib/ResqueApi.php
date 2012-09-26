<?php
/**
 * ResqueApi class
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

namespace ResqueBoard\Lib;

/**
 * ResqueApi Class
 *
 * Manage Resque workers
 *
 * @author Wan Qi Chen <kami@kamisama.me>
 */
class ResqueApi
{
    protected $runtime = array();

    protected $errors = array();

    /**
     *
     * @params String $configPath Absolute path to Resque config file
     */
    public function __construct($configPath)
    {
        if (!file_exists($configPath)) {
            trigger_error("Resque configuration file not found");
        }

        $this->runtime = parse_ini_file($configPath, true);

        if (substr($this->runtime['Resque']['lib'], 0, 2) == './' || substr($this->runtime['Resque']['lib'], 0, 3) == '../') {
            $lib = realpath(pathinfo($configPath, PATHINFO_DIRNAME)) . DIRECTORY_SEPARATOR . $this->runtime['Resque']['lib'];
        } else {
            $lib = $this->runtime['Resque']['lib'];
        }
    }


    /**
     * Start workers
     *
     * Start one or multiple worker from a set of property
     *
     * @params array $args Worker properties
     * @return array An array of workers ID that were started
     */
    public function start($args = array())
    {
        if ($this->validate($args)) {
            $cmd = implode(
                ' ',
                array(
                    sprintf("nohup  "),
                    sprintf('bash -c "cd %s; ', escapeshellarg($this->runtime['Resque']['lib'])),
                    sprintf("VVERBOSE=true QUEUE=%s ", escapeshellarg($this->runtime['Worker']['queue'])),
                    sprintf("APP_INCLUDE=%s INTERVAL=%s ", escapeshellarg($this->runtime['Resque']['include']), (int)$this->runtime['Worker']['interval']),
                    sprintf("REDIS_BACKEND=%s ", escapeshellarg($this->runtime['Redis']['host'] . ':' .$this->runtime['Redis']['port'])),
                    sprintf("REDIS_DATABASE=%s REDIS_NAMESPACE=%s", (int)$this->runtime['Redis']['database'], escapeshellarg($this->runtime['Redis']['namespace'])),
                    sprintf("COUNT=%s ", (int)$this->runtime['Worker']['workers']),
                    sprintf("LOGHANDLER=%s LOGHANDLERTARGET=%s ", escapeshellarg($this->runtime['Log']['handler']), escapeshellarg($this->runtime['Log']['target'])),
                    sprintf("php ./resque.php >> %s", escapeshellarg($this->runtime['Log']['filename'])),
                    '2>&1" >/dev/null 2>&1 &'
                )
            );

            passthru($cmd);

            return true;
        }
        return false;
    }

    /**
     * Stop workers
     *
     * @params array $workersId An array of worker ID to stop
     * @return bool Always return true
     */
    public function stop($workersId = array())
    {
        $workers = \Resque_Worker::all();

        if (!is_array($workersId)) {
            $workersId = array($workersId);
        }

        foreach ($workers as $worker) {
            if (in_array((string)$worker, $workersId)) {
                list($hostname, $pid, $queue) = explode(':', $worker);
                $worker->shutDown();    // Send signal to stop processing jobs
                $worker->unregisterWorker();                                            // Remove jobs from resque environment

                $output = array();
                $message = exec('kill -9 ' . $pid . ' 2>&1', $output, $code);   // Kill all remaining system process
            }
        }
        return true;
    }


    /**
     * Get workers infos
     *
     * @params array $workersId An array of worker ID
     * @return array An associative array of worker's info
     */
    public function getInfos($workersId = array())
    {

    }


    /**
     * Enqueue a job into a queue
     *
     * @params $queue   String  Queue name
     * @params $job     String  Job class
     * @params $args    Array   Job arguments
     * @return String|bool      False if enqueue failed, job ID on success
     */
    public function enqueue($queue, $job, $args = array())
    {

    }

    /**
     * Validate a set of worker properties
     */
    private function validate($args)
    {
        $default = array(
            'user' => $this->runtime['Worker']['user'],
            'interval' => $this->runtime['Worker']['interval'],
            'workers' => $this->runtime['Worker']['workers'],
            'log' => $this->runtime['Log']['filename'],
            'host' => $this->runtime['Redis']['host'],
            'port' => $this->runtime['Redis']['port'],
            'database' => $this->runtime['Redis']['database'],
            'namespace' => $this->runtime['Redis']['namespace'],
            'handler' => $this->runtime['Log']['handler'],
            'target' => $this->runtime['Log']['target'],
            'queues' => $this->runtime['Worker']['queue'],
            'include' => $this->runtime['Resque']['include']
            );

        $options = array_merge($default, array_filter(array_intersect_key($args, $default)));

        array_walk(
            $options,
            function (&$option) {
                $option = trim($option);
            }
        );

        // Trim unecessary spaces between comma
        $options['queues'] = implode(
            ',',
            array_map(
                function ($q) {
                    return trim($q);
                },
                explode(',', $options['queues'])
            )
        );

        $this->runtime['Redis']['host'] = $options['host'];
        $this->runtime['Redis']['port'] = $options['port'];
        $this->runtime['Redis']['database'] = $options['database'];
        $this->runtime['Redis']['namespace'] = $options['namespace'];

        $this->runtime['Worker']['queue'] = $options['queues'];
        $this->runtime['Worker']['interval'] = $options['interval'];
        $this->runtime['Worker']['workers'] = $options['workers'];
        $this->runtime['Worker']['user'] = $options['user'];

        $this->runtime['Log']['filename'] = $options['log'];
        $this->runtime['Log']['handler'] = $options['handler'];
        $this->runtime['Log']['target'] = $options['target'];

        $this->runtime['Resque']['include'] = $options['include'];
        $this->runtime['Resque']['lib'] = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'kamisama' . DIRECTORY_SEPARATOR . 'php-resque-ex';

        if ($this->runtime['Worker']['interval'] !== '') {
            if ($this->runtime['Worker']['interval'] == 0) {
                $this->errors['interval'] = 'Polling frequency must be equal or greater than 1 second';
            }
            if (!is_numeric($this->runtime['Worker']['interval'])) {
                $this->errors['interval'] = 'Polling frequency must be numeric';
            }
        }

        if (!empty($this->runtime['Worker']['user'])) {
            $output = array();
            exec('id ' . $this->runtime['Worker']['user'] . ' 2>&1', $output, $status);
            if ($status != 0) {
                $this->errors['user'] = sprintf('User %s does not exists', $this->runtime['Worker']['user']);
            }
        }

        if ($options['workers'] !== '') {
            if (empty($this->runtime['Worker']['workers'])) {
                $this->errors['workers'] = 'Worker count must be equal or greater than 1';
            }
            if (!is_numeric($this->runtime['Worker']['workers'])) {
                $this->errors['workers'] = 'Polling frequency must be numeric';
            }
        }

        if ($this->runtime['Redis']['port'] !== '') {
            if (!is_numeric($this->runtime['Redis']['port'])) {
                $this->errors['port'] = 'Redis port must be numeric';
            }
        }

        if ($this->runtime['Redis']['database'] !== '') {
            if (!is_numeric($this->runtime['Redis']['database'])) {
                $this->errors['database'] = 'Redis database must be numeric';
            }
        }

        if ($this->runtime['Log']['target'] !== '' && $this->runtime['Log']['handler'] !== '') {
            if (empty($this->runtime['Log']['target'])) {
                $this->errors['target'] = 'Target can not be empty';
            }
        }

        if ($this->runtime['Resque']['include'] !== '') {
            if (substr($this->runtime['Resque']['include'], 0, 1) !== '/') {
                $this->errors['include'] = 'Autoloader path must be an absolute path';
            } else if (!file_exists($this->runtime['Resque']['include'])) {
                $this->errors['include'] = 'Autoloader file does not exists';
            }
        }

        if (!file_exists($this->runtime['Resque']['lib'])) {
            $this->errors['lib'] = 'PhpResque library not found';
        }

        if ($this->runtime['Log']['filename'] !== '') {
            if (substr($this->runtime['Log']['filename'], 0, 1) !== '/') {
                $this->errors['log'] = 'Log path must be an absolute path';
            }

            $path = pathinfo($this->runtime['Log']['filename'], PATHINFO_DIRNAME);
            if (!file_exists($path)) {
                $this->errors['log'] = 'Directory for log file does not exists';
            } else if (!is_writable($path)) {
                $this->errors['log'] = 'Directory for log file is not writeable';
            }

        } else {
            $this->errors['log'] = 'Log path can not be empty';
        }

        return empty($this->errors);

    }


    /**
     * Return an array of validation errors
     *
     * @return an associative array of validation errors
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
