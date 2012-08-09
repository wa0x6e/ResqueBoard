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
    
    $app->get('/workers', function () use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);
            
            $app->render('workers.php', array(
                'workers' => $resqueStat->getWorkers(),
                'pageTitle' => 'Active workers' . TITLE_SEP . APPLICATION_NAME
            ));
            
        } catch (\Exception $e) {
            $app->error($e);
        }
    });
    
    $app->map('/jobs', function () use ($app, $settings) {
    	try {
    		$resqueStat = new ResqueBoard\Lib\ResqueStat($settings);
    
    		$jobs = array();
    		$searchToken = null;
    		
    		if ($app->request()->isPost())
    		{
    			if ($app->request()->post('job_id') != null)
    			{
    				$jobId = $searchToken = ltrim($app->request()->post('job_id'), '#');
    				$jobs = $resqueStat->getJob($jobId);
    			}
    			
    		}
    		$jobs = $resqueStat->getJob('211d76cc08e7b5f6d623fb2319803519');
    		$app->render('jobs.php', array(
    			'jobs' => $jobs,
    			'searchToken' => $searchToken,
    			'workers' => $resqueStat->getWorkers(),
    			'pageTitle' => 'Jobs' . TITLE_SEP . APPLICATION_NAME
    		));
    
    	} catch (\Exception $e) {
    		$app->error($e);
    	}
    })->via('GET', 'POST');
    
    $app->get('/jobs/:workerHost/:workerProcess', function ($workerHost, $workerProcess) use ($app, $settings) {
    	try {
    		$resqueStat = new ResqueBoard\Lib\ResqueStat($settings);
    
    		$page = 1;
    		$limit = 15;
    		$workerId = $workerHost . ':' . $workerProcess;
    		
    		$app->render('jobs.php', array(
    						'jobs' => $resqueStat->getJobsByWorker($workerId, $page, $limit),
    						'searchToken' => $workerId,
    						'workers' => $resqueStat->getWorkers(),
    						'pageTitle' => 'Jobs' . TITLE_SEP . APPLICATION_NAME
    		));
    
    	} catch (\Exception $e) {
    		$app->error($e);
    	}
    });
    
    $app->get('/api/jobs/:start/:end', function ($start, $end) use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);
            $jobs = array_values($resqueStat->getJobs($start, $end, false));
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