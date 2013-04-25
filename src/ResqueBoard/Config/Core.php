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

if (!defined('ROOT')) {
    define('ROOT', dirname(dirname(__FILE__)));
}

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

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
            'cube' => array(
                'host' => '127.0.0.1',
                'port' => 1081
            ),
            'cubePublic' => array(
                'host' => '127.0.0.1',
                'port' => 1081
            ),
            /*'resquePrefix' => 'resque',*/
            'readOnly' => true,
            'resqueConfig' => __DIR__ . DIRECTORY_SEPARATOR . './resque.ini',
            /*'timezone' => 'America/Montreal'*/
    );

    /**
     * Datas used for instanciating a Slim object
     *
     * @see http://www.slimframework.com/documentation/stable#settings
     * @var array
     */
    $config = array(
                'debug' => false,
                'view' => 'ResqueBoard\View\MyView',
                'templates.path' => ROOT . DS .'View'
            );


    /**
     * Map a log level to an html/css class name
     *
     * @var array
     */
    $logLevels = array(
            100 => array('class' => 'label-success', 'name' => 'debug'),
            200 => array('class' => 'label-info', 'name' => 'info'),
            300 => array('class' => 'label-warning', 'name' => 'warning'),
            400 => array('class' => 'label-important', 'name' => 'error'),
            500 => array('class' => 'label-inverse', 'name' => 'critical'),
            550 => array('class' => 'label-inverse', 'name' => 'alert')
    );

    /**
     * List of events type
     *
     * @var array
     */
    $logTypes = array('start', 'got', 'process', 'fork', 'done', 'fail', 'sleep', 'prune', 'stop', 'pause', 'resume');

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
     * Cube server address without the scheme (http, https, ws, ...)
     *
     * @since  1.5.1
     * @var string
     */
    define('CUBE_URL', $settings['cubePublic']['host'] . ':' . $settings['cubePublic']['port']);

    /**
     * Separator between the website name and other text, in the page title
     *
     * @var string
     */
    define('TITLE_SEP', ' | ');

    $settings['nav'] = array(
        'index' => array(
            'icon' => 'icon-home',
            'name' => 'Home',
            'title' => 'Dashboard',
            'link' => '/'
            ),
        'workers' => array(
            'icon' => 'icon-cogs',
            'name' => 'Workers',
            'title' => 'Workers',
            'link' => '/workers'
        ),
        'logs' => array(
            'icon' => 'icon-file',
            'name' => 'Logs',
            'title' => 'Logs',
            'link' => '/logs',
            'submenu' => array(
                'tail' => array(
                    'icon' => 'icon-eye-open',
                    'name' => 'Latest logs',
                    'title' => 'Logs',
                    'link' => '/logs'
                ),
                'browser' => array(
                    'icon' => 'icon-eye-open',
                    'name' => 'Logs browser',
                    'title' => 'Logs browser',
                    'link' => '/logs/browse'
                )
            )
        ),
        'jobs' => array(
                'icon' => 'icon-briefcase',
                'name' => 'Jobs',
                'title' => 'Jobs Dashboard',
                'link' => '/jobs',
                'submenu' => array(
                    'dashboard' => array(
                        'icon' => 'icon-dashboard',
                        'name' => 'Jobs Dashboard',
                        'title' => 'Jobs',
                        'link' => '/jobs'
                    ),
                    '',
                    'view_processed' => array(
                        'icon' => 'icon-briefcase',
                        'name' => 'Processed jobs',
                        'title' => 'Processed jobs',
                        'link' => '/jobs/view'
                    ),
                    'view_pending' => array(
                        'icon' => 'icon-briefcase',
                        'name' => 'Pending jobs',
                        'title' => 'Pending jobs',
                        'link' => '/jobs/pending'
                    ),
                    'view_scheduled' => array(
                        'icon' => 'icon-briefcase',
                        'name' => 'Scheduled jobs',
                        'title' => 'Scheduled jobs',
                        'link' => '/jobs/scheduled'
                    ),
                    '',
                    'class_distribution' => array(
                        'icon' => 'icon-tasks',
                        'name' => 'Class distribution',
                        'title' => 'Class distribution',
                        'link' => '/jobs/distribution/class'
                    ),
                    'load_distribution' => array(
                        'icon' => 'icon-table',
                        'name' => 'Load distribution',
                        'title' => 'Load distribution',
                        'link' => '/jobs/distribution/load'
                    ),
                    'load_overview' => array(
                        'icon' => 'icon-bar-chart',
                        'name' => 'Load overview',
                        'title' => 'Load overview',
                        'link' => '/jobs/overview/hour'
                    )
                )
         )
    );


    require dirname(dirname(ROOT)) . DS . 'vendor' . DS . 'autoload.php';
