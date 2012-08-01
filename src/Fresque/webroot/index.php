<?php
    
    
    if (!defined('ROOT')) {
        define('ROOT', dirname(dirname(__FILE__)));
    }
    
    if (!defined('DS')) {
        define('DS', DIRECTORY_SEPARATOR);
    }
    
   
    include(ROOT . DS . 'Config' . DS . 'Core.php');
    
    $app = new Slim($config);
    
    $app->get('/', function () use ($app, $settings) {
        try {
            $resqueStat = new Fresque\Lib\ResqueStat($settings);
            
            $app->render('index.php', array(
                'stats' => $resqueStat->getStats(),
                'workers' => $resqueStat->getWorkers(),
                'queues' => $resqueStat->getQueues(),
                'pageTitle' => APPLICATION_NAME
            ));
            
        } catch (\Exception $e) {
            $app->error($e);
        }
       
    });
    
    $app->get('/logs', function () use ($app) {
        $app->render('logs.php', array(
                'pageTitle' => 'Logs' . TITLE_SEP . APPLICATION_NAME
                ));
    });
    
    $app->get('/working', function () use ($app, $settings) {
        try {
            $resqueStat = new Fresque\Lib\ResqueStat($settings);
            
            $app->render('working.php', array(
                'workers' => $resqueStat->getWorkers(),
                'pageTitle' => 'Active workers' . TITLE_SEP . APPLICATION_NAME
            ));
            
        } catch (\Exception $e) {
            $app->error($e);
        }
    });
    
    
    $app->error(function (\Exception $e) use ($app) {
        $app->render('error.php', array(
                'pageTitle' => 'Error' . TITLE_SEP . APPLICATION_NAME,
                'message' => $e->getMessage()
            ));
    });
    
    
    $app->run();