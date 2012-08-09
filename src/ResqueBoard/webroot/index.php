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
                'pageTitle' => 'Logs'
            ));
    });
    
    $app->get('/workers', function () use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);
            
            $app->render('workers.php', array(
                'workers' => $resqueStat->getWorkers(),
                'pageTitle' => 'Active workers'
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
    		
    		$limit = 15;
    		
    		if ($app->request()->isPost())
    		{
    			if ($app->request()->post('job_id') != null)
    			{
    				$jobId = $searchToken = ltrim($app->request()->post('job_id'), '#');
    				$jobs = $resqueStat->getJob($jobId);
    			}
    			
    		}
    		$jobs = $resqueStat->getJobsByWorker(null, 1, $limit);
    		$app->render('jobs.php', array(
    			'jobs' => $jobs,
    			'searchToken' => $searchToken,
    			'workers' => $resqueStat->getWorkers(),
    			'pageTitle' => 'Last '.$limit.' Jobs'
    		));
    
    	} catch (\Exception $e) {
    		$app->error($e);
    	}
    })->via('GET', 'POST');
    
    $app->get('/jobs/:workerHost/:workerProcess(/:limit(/:page))', function ($workerHost, $workerProcess, $limit = 15, $page = 1) use ($app, $settings) {
    	try {
    		$resqueStat = new ResqueBoard\Lib\ResqueStat($settings);
    
    		$workerId = $workerHost . ':' . $workerProcess;
    		
    		$pagination = new stdClass();
    		$pagination->current = $page;
    		$pagination->limit = $limit;
    		$pagination->baseUrl = '/jobs/' . $workerHost . '/' . $workerProcess . '/';
    		$pagination->totalResult = $resqueStat->getJobsByWorkersCount($workerId);
    		$pagination->totalPage = ceil($pagination->totalResult / $limit);
    		
    		
    		$app->render('jobs.php', array(
    						'jobs' => $resqueStat->getJobsByWorker($workerId, $page, $limit),
    						'searchToken' => $workerId,
    						'workers' => $resqueStat->getWorkers(),
    						'pageTitle' => 'Jobs',
    						'pagination' => $pagination
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
                'pageTitle' => 'Error',
                'message' => $e->getMessage()
            ));
    });
    
    
    $app->run();