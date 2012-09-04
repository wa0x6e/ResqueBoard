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

define('APPLICATION_VERSION', '1.1.0');


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

$app->get(
    '/jobs',
    function () use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);

            $app->render(
                'jobs.ctp',
                array(
                    'jobs' => $resqueStat->getJobs(array('limit' => PAGINATION_LIMIT)),
                    'failedJobs' =>  $resqueStat->getJobs(
                        array(
                            'status' => ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED,
                            'limit' => 10
                        )
                    ),
                    'jobsStats' => $resqueStat->getJobsStats(),
                    'jobsRepartitionStats' => $resqueStat->getJobsRepartionStats(),
                    'workers' => $resqueStat->getWorkers(),
                    'resultLimits' => array(15, 50, 100),
                    'pageTitle' => 'Jobs'
                )
            );

        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);

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

$app->map(
    '/jobs/view',
    function () use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);
            $errors  = array();

            $activeWorkers = $resqueStat->getWorkers();

            $resultLimits = array(15, 50, 100);

            $defaults = array(
                    'page' => 1,
                    'limit' => $resultLimits[0],
                    'class' => null,
                    'queue' => null,
                    'date_after' => null,
                    'date_before' => null,
                    'worker' => array(),
                    'workers' => ''
                );

            $searchData = array_merge($defaults, $app->request()->params());
            array_walk(
                $searchData,
                function (&$key) {
                    if (is_string($key)) {
                        $key = trim($key);
                    }
                }
            );

            $pagination = new stdClass();
            $pagination->current = $searchData['page'];
            $pagination->limit = (($app->request()->params('limit') != '') && in_array($app->request()->params('limit'), $resultLimits))
                ? $app->request()->params('limit')
                : PAGINATION_LIMIT;
            $pagination->baseUrl = '/jobs/view?';

            $conditions = array();
            $searchToken = '';
            if ($app->request()->isPost() && $app->request()->post('job_id') != null) {
                $jobId = $searchToken = ltrim($app->request()->post('job_id'), '#');
                $jobs = $resqueStat->getJobs(array('jobId' => $jobId));
            } else {
                $conditions = array(
                    'page' => $searchData['page'],
                    'limit' => $searchData['limit'],
                    'class' => $searchData['class'],
                    'queue' => $searchData['queue'],
                    'date_after' => $searchData['date_after'],
                    'date_before' => $searchData['date_before'],
                    'worker' => $searchData['worker']
                );

                if (!empty($searchData['workers'])) {
                    $conditions['worker'] += array_map('trim', explode(',', $searchData['workers']));
                }

                // Validate search datas
                $dateTimePattern = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])(\s+?(0[1-9]|1[0-9]|2[0-4]):([0-5][0-9])(:([0-5][0-9]))*?)*?$/';
                if (!empty($conditions['date_after']) && preg_match($dateTimePattern, $conditions['date_after']) == 0) {
                    $errors['date_after'] = 'Date is not valid';
                }
                if (!empty($conditions['date_before']) && preg_match($dateTimePattern, $conditions['date_before']) == 0) {
                    $errors['date_before'] = 'Date is not valid';
                }
                if ($conditions['worker']) {

                }

                if (empty($errors)) {
                    $jobs = $resqueStat->getJobs($conditions);
                } else {
                    $jobs = array();
                }
            }

            $pagination->totalResult = $resqueStat->getJobs(array_merge($conditions, array('type' => 'count')));
            $pagination->totalPage = ceil($pagination->totalResult / $pagination->limit);
            $pagination->uri = $app->request()->params();

            $app->render(
                'jobs_view.ctp',
                array(
                    'jobs' => $jobs,
                    'workers' => $activeWorkers,
                    'resultLimits' => $resultLimits,
                    'pageTitle' => 'Jobs',
                    'errors' => $errors,
                    'searchData' => $searchData,
                    'searchToken' => $searchToken,
                    'pagination' => $pagination
                )
            );

        } catch (\Exception $e) {
            $app->error($e);
        }
    }
)->via('GET', 'POST');

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

