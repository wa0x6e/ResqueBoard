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
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);
            
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
    
    $app->get('/logs', function () use ($app, $logLevels, $logTypes) {
        
        $mutedLevels = $app->getCookie('ResqueBoard.mutedLevel');
        if (empty($mutedLevels)) {
            $app->setCookie('ResqueBoard.mutedLevel', '', '1 year');
        }
        
        $mutedLevels = array_filter(explode(',', $mutedLevels));
        
        $app->render('logs.php', array(
                'logLevels' => $logLevels,
                'logTypes' => $logTypes,
                'mutedLevels' => $mutedLevels,
                'pageTitle' => 'Logs' . TITLE_SEP . APPLICATION_NAME
            ));
    });
    
    $app->get('/working', function () use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);
            
            $app->render('working.php', array(
                'workers' => $resqueStat->getWorkers(),
                'pageTitle' => 'Active workers' . TITLE_SEP . APPLICATION_NAME
            ));
            
        } catch (\Exception $e) {
            $app->error($e);
        }
    });
    
    $app->get('/api/jobs/:start/:end', function ($start, $end) use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);
            $jobs = $resqueStat->getJobs($start, $end);
            $app->response()->header("Content-Type", "application/json");
            echo json_encode($jobs);
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