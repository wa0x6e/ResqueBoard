<?php
/**
 * Mongo service class
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
 * Mongo service class
 *
 * @subpackage      resqueboard.lib.service
 * @since            1.0.0
 * @author           Wan Qi Chen <kami@kamisama.me>
 */
class Mongo extends AbstractService
{
    public static $instance = null;

    public function __construct($settings)
    {
        parent::bootstrap();

        $t = microtime(true);
        parent::$serviceInstance[get_class()] = new \Mongo($settings['host'] . ':' . $settings['port']);

        $queryTime = round((microtime(true) - $t) * 1000, 2);
        parent::logQuery(
            array(
                'command' => 'CONNECTION to ' . $settings['host'] . ':' . $settings['port'],
                'time' => $queryTime
            )
        );
        parent::$_totalTime[get_class()] += $queryTime;
        parent::$_totalQueries[get_class()]++;
    }

    public static function init($settings)
    {
        if (self::$instance === null) {
            self::$instance = new Mongo($settings);
        }

        return self::$instance;
    }
}
