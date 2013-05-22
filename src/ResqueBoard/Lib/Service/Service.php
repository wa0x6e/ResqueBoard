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
class Service
{
    public static $services = array();

    public static $settings = array();

    public static function __callStatic($function, $arguments)
    {
        if (!isset(self::$services[$function])) {
            self::$services[$function] = call_user_func_array('ResqueBoard\Lib\Service\\' . $function . '::init', array(self::$settings[$function]));
        }

        return self::$services[$function];
    }
}
