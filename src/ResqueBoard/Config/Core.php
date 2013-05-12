<?php
/**
 * Core configuration file
 *
 * Use to configure the behavior of ResqueBoard
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
 * @subpackage	  resqueboard.config
 * @since         1.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */



/**
 * General settings
 *
 * Uncomment to override the default settings.
 *
 * $resquePrefix is the prefix php-resque prepends to all its keys.
 *
 * @var array
 */
$settings = array(
        /*'mongo' => array(
         	'host' => 'localhost',
            'port' => 27017,
            'database' => 'cube_development'
        ),*/
        /*'redis' => array(
         	'host' => '127.0.0.1',
         	'port' => 6379,
         	'database' => 0
        ),*/
      /* 'cube' => array(
            'host' => '127.0.0.1',
            'port' => 1081
        ),*/
        'cubePublic' => array(
            'host' => $_SERVER['SERVER_NAME'],
            'port' => 1081
        ),
        /*'resquePrefix' => 'resque',*/
        'readOnly' => true,
        'resqueConfig' => __DIR__ . DIRECTORY_SEPARATOR . './resque.ini',
        /*'timezone' => 'America/Montreal'*/
);

date_default_timezone_set(isset($settings['timezone']) ? $settings['timezone'] : date_default_timezone_get());

/**
 * Default number of items to display for pagination
 *
 * @var int
 */
define('PAGINATION_LIMIT', 15);

/**
 * Default application name
 *
 * @used for the website title
 * @var string
 */
define('APPLICATION_NAME', 'ResqueBoard');

/**
 * Separator between the website name and other text, in the page title
 *
 * @var string
 */
define('TITLE_SEP', ' | ');
