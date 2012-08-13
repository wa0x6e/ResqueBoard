<?php

    include(dirname(dirname(ROOT)) . DS . 'vendor' . DS . 'autoload.php');
    
    define('APPLICATION_NAME', 'ResqueBoard');
    define('TITLE_SEP', ' | ');
    
    $settings = array(
            /*'mongo' => array(
             'host' => 'localhost',
                    'port' => 27017,
                    'database' => 'cube_development'
            ),*/
            /* 'redis' => array(
             'host' => '127.0.0.1',
                    'port' => 6379
            ),*/
            /*'resquePrefix' => 'resque'*/
    );
    
    $config = array(
                'debug' => false,
                'view' => 'ResqueBoard\View\MyView',
                'templates.path' => ROOT . DS .'View'
            );
    
    $logLevels = array(
            'debug' => 'label-success',
            'info' => 'label-info',
            'warning' => 'label-warning',
            'error' => 'label-important',
            'criticial' => 'label-inverse',
            'alert' => 'label-inverse'
    );
    
    $logTypes = array('start', 'got', 'process', 'fork', 'done', 'fail', 'sleep', 'prune', 'stop');
    
    define('PAGINATION_LIMIT', 15);