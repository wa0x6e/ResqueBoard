<?php
/**
 * Redis service class
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
 * @since         1.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\Lib\Service;

/**
 * Redis service class
 *
 * @subpackage      resqueboard.lib.service
 * @since            1.0.0
 * @author           Wan Qi Chen <kami@kamisama.me>
 */
trait Loggable
{
    protected static $_logs = array();

    protected static $_totalTime = 0;

    protected static $_totalQueries = 0;

    protected static $_maxLogs = 200;

    public function __call($name, $args)
    {
        $t = microtime(true);
        $result = call_user_func_array(array(self::$serviceInstance, $name), $args);
        $queryTime = round((microtime(true) - $t) * 1000, 2);
        self::logQuery(
            array(
                'command' => strtoupper($name) . ' ' . multiImplode(' ', $args),
                'time' => $queryTime
            )
        );
        self::$_totalTime += $queryTime;
        self::$_totalQueries++;
        return $result;
    }

    protected static function logQuery($log)
    {
        $trace = debug_backtrace();

        $log['trace']['file'] = $trace[1]['file'];
        $log['trace']['line'] = $trace[1]['line'];

        self::$_logs[] = $log;
        if (count(self::$_logs) > self::$_maxLogs) {
            array_shift(self::$_logs);
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
            'count' => self::$_totalQueries,
            'time' => self::$_totalTime,
            'logs' => self::$_logs
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
