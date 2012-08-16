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
     * Database connection settings
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
             	'port' => 6379
            ),*/
            /*'resquePrefix' => 'resque'*/
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
            'debug' => 'label-success',
            'info' => 'label-info',
            'warning' => 'label-warning',
            'error' => 'label-important',
            'criticial' => 'label-inverse',
            'alert' => 'label-inverse'
    );
    
    
    /**
     * List of events type
     *
     * @var array
     */
    $logTypes = array('start', 'got', 'process', 'fork', 'done', 'fail', 'sleep', 'prune', 'stop');
    
    
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
    
    include(dirname(dirname(ROOT)) . DS . 'vendor' . DS . 'autoload.php');