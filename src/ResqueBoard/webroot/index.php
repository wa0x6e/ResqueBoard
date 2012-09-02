<?php
/**
 * Index file
 *
 * Application entry point, defines routes
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
 * @subpackage      resqueboard.webroot
 * @since         1.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.ctp)
 */

if (!defined('ROOT')) {
    define('ROOT', dirname(dirname(__FILE__)));
}

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

define('APPLICATION_VERSION', '1.0.0');


include ROOT . DS . 'Config' . DS . 'Core.php';

$app = new Slim($config);

$app->get(
    '/',
    function () use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);

            $app->render(
                'index.ctp',
                array(
                    'stats' => $resqueStat->getStats(),
                    'workers' => $resqueStat->getWorkers(),
                    'queues' => $resqueStat->getQueues(),
                    'pageTitle' => APPLICATION_NAME
                )
            );

        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);

$app->get(
    '/logs',
    function () use ($app, $logLevels, $logTypes) {

        $mutedLevels = $app->getCookie('ResqueBoard.mutedLevel');
        if (empty($mutedLevels)) {
            $app->setCookie('ResqueBoard.mutedLevel', '', '1 year', '/logs');
        }

        $mutedLevels = array_filter(explode(',', $mutedLevels));

        $app->render(
            'logs.ctp',
            array(
                'logLevels' => $logLevels,
                'logTypes' => $logTypes,
                'mutedLevels' => $mutedLevels,
                'pageTitle' => 'Logs'
            )
        );
    }
);

$app->get(
    '/workers',
    function () use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);

            $app->render(
                'workers.ctp',
                array(
                    'workers' => $resqueStat->getWorkers(),
                    'pageTitle' => 'Active workers'
                )
            );

        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);

$app->map(
    '/jobs',
    function () use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);

            $jobs = array();
            $searchToken = null;

            $limit = PAGINATION_LIMIT;

            $resultLimits = array(15, 50, 100);

            if ($app->request()->isPost()) {
                if ($app->request()->post('job_id') != null) {
                    $jobId = $searchToken = ltrim($app->request()->post('job_id'), '#');
                    $jobs = $resqueStat->getJob($jobId);
                }

            }
            $jobs = $resqueStat->getJobsByWorker(null, 1, $limit);
            $app->render(
                'jobs.ctp',
                array(
                    'jobs' => $jobs,
                    'jobsStats' => $resqueStat->getJobsStats(),
                    'jobsRepartitionStats' => $resqueStat->getJobsRepartionStats(),
                    'searchToken' => $searchToken,
                    'workers' => $resqueStat->getWorkers(),
                    'resultLimits' => $resultLimits,
                    'pageTitle' => 'Jobs'
                )
            );

        } catch (\Exception $e) {
            $app->error($e);
        }
    }
)->via('GET', 'POST');

$app->get(
    '/jobs/distribution',
    function () use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);

            $app->render(
                'jobs_distribution.ctp',
                array(
                    'jobsRepartitionStats' => $resqueStat->getJobsRepartionStats(null),
                    'pageTitle' => 'Jobs distribution'
                )
            );

        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);

$app->get(
    '/jobs/:workerHost/:workerProcess(/:limit(/:page))',
    function ($workerHost, $workerProcess, $limit = PAGINATION_LIMIT, $page = 1) use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);

            $workerId = $workerHost . ':' . $workerProcess;

            $resultLimits = array(15, 50, 100);

            $pagination = new stdClass();
            $pagination->current = $page;
            $pagination->limit = (in_array($limit, $resultLimits)) ? $limit : PAGINATION_LIMIT;
            $pagination->baseUrl = '/jobs/' . $workerHost . '/' . $workerProcess . '/';
            $pagination->totalResult = $resqueStat->getJobsByWorkersCount($workerId);
            $pagination->totalPage = ceil($pagination->totalResult / $pagination->limit);


            $app->render(
                'jobs.ctp',
                array(
                    'jobs' => $resqueStat->getJobsByWorker($workerId, $page, $pagination->limit),
                    'searchToken' => $workerId,
                    'workers' => $resqueStat->getWorkers(),
                    'resultLimits' => $resultLimits,
                    'pageTitle' => 'Jobs',
                    'pagination' => $pagination
                )
            );

        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);

$app->get(
    '/api/jobs/:start/:end',
    function ($start, $end) use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);
            $jobs = array_values($resqueStat->getJobs($start, $end, false));
            $app->response()->header("Content-Type", "application/json");
            echo json_encode($jobs);
        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);


$app->error(
    function (\Exception $e) use ($app) {
        $app->render(
            'error.ctp',
            array(
                'pageTitle' => 'Error',
                'message' => $e->getMessage()
            )
        );
    }
);

$app->run();

