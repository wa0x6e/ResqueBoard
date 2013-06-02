<?php
/**
 * Abstract Service class
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package    ResqueBoard
 * @subpackage ResqueBoard.Lib.Service
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @copyright  2012-2013 Wan Qi Chen
 * @link       http://resqueboard.kamisama.me
 * @since      2.0.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\Lib\Service;

/**
 * Abstract Service class
 *
 * Implements function to log all services activities
 *
 * @subpackage ResqueBoard.Lib.Service
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @since      2.0.0
 */
abstract class AbstractService
{
    protected static $_logs = array();

    protected static $_totalTime = array();

    protected static $_totalQueries = array();

    protected static $_maxLogs = 200;

    public static $serviceInstance = array();

    public function __call($name, $args)
    {
        $t = microtime(true);
        $result = call_user_func_array(array(self::$serviceInstance[get_called_class()], $name), $args);
        $queryTime = round((microtime(true) - $t) * 1000, 2);
        self::logQuery(
            array(
                'command' => strtoupper($name) . ' ' . multiImplode(' ', $args),
                'time' => $queryTime
            )
        );
        self::$_totalTime[get_called_class()] += $queryTime;
        self::$_totalQueries[get_called_class()]++;
        return $result;
    }

    protected function bootstrap()
    {
        self::$_totalTime[get_called_class()] = 0;
        self::$_totalQueries[get_called_class()] = 0;
        self::$_logs[get_called_class()] = array();
    }

    protected static function logQuery($log)
    {
        $trace = debug_backtrace();

        $log['trace']['file'] = $trace[1]['file'];
        $log['trace']['line'] = $trace[1]['line'];

        self::$_logs[get_called_class()][] = $log;
        if (count(self::$_logs[get_called_class()]) > self::$_maxLogs) {
            array_shift(self::$_logs[get_called_class()]);
        }
    }

    /**
     * Return the logs
     *
     * @return array An array of queries
     */
    public static function getLogs()
    {
        return array(
            'count' => self::$_totalQueries[get_called_class()],
            'time' => self::$_totalTime[get_called_class()],
            'logs' => self::$_logs[get_called_class()]
        );
    }
}

function multiImplode($glue, $pieces)
{
    $string = '';

    if (is_array($pieces)) {
        reset($pieces);
        while (list($key, $value) = each($pieces)) {
            $string .= $glue . multiImplode($glue, $value);
        }
    } else {
        return $pieces;
    }
    return trim($string, $glue);
}
