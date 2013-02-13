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

define('APPLICATION_VERSION', '1.5.0');



include ROOT . DS . 'Config' . DS . 'Core.php';

$app = new Slim\Slim($config);

$app->get(
    '/',
    function () use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);
            $resqueApi = new ResqueBoard\Lib\ResqueApi($settings['resqueConfig']);
            $app->render(
                'index.ctp',
                array(
                    'stats' => $resqueStat->getStats(),
                    'workers' => $resqueStat->getWorkers(),
                    'schedulerWorkers' => $resqueStat->getSchedulerWorkers(),
                    'queues' => $resqueStat->getActiveQueues(),
                    'pageTitle' => 'Home',
                    'readOnly' => $settings['readOnly']
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
                    'pageTitle' => 'Active workers',
                    'readOnly' => $settings['readOnly']
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
                    'pendingJobs' => $resqueStat->getJobs(array('status' => ResqueBoard\Lib\ResqueStat::JOB_STATUS_WAITING, 'limit' => 15)),
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
    '/jobs/distribution/class',
    function () use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);

            $app->render(
                'jobs_class_distribution.ctp',
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
    '/jobs/distribution/load(/:year/:month)',
    function ($year = null, $month = null) use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);

            if ($year === null && $month === null) {
                $start = new \DateTime();
                $start->modify('first day of this month');
                $start->setTime(0, 0, 0);
            } else {
                $start = new \DateTime($year . '-' . $month . '-01');
            }

            $end = clone $start;
            $end->modify('last day of ' . $end->format('F') . ' ' . $end->format('Y'));
            $end->setTime(23, 59, 59);

            $firstJob = $resqueStat->getJobs(array('limit' => 1, 'sort' => array('t' => 1), 'format' => false));

            $app->render(
                'jobs_load_distribution.ctp',
                array(
                    'jobsMatrix' => $resqueStat->getJobsMatrix($start, $end, ResqueBoard\Lib\ResqueStat::CUBE_STEP_1DAY),
                    'pageTitle' => 'Jobs load distribution',
                    'startDate' => new DateTime(current($firstJob)['time']),
                    'currentDate' => $start
                )
            );

        } catch (\Exception $e) {
            $app->error($e);
        }
    }
)->conditions(array('year' => '(19|20)\d\d', 'month' => '(0\d|1[0-2])'));

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

            $pagination->totalResult = $resqueStat->getJobs(array_merge($conditions, array('type' => 'count')));

            if ($app->request()->params('job_id') != null) {
                $jobId = $searchToken = ltrim($app->request()->params('job_id'), '#');
                $jobs = $resqueStat->getJobs(array('jobId' => $jobId));
                $pagination->totalResult = $resqueStat->getJobs(array_merge(array('jobId' => $jobId), array('type' => 'count')));
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
                $dateTimePattern = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])?(\s+([0-1][0-9]|2[0-4]):[0-5]\d(:[0-5]\d)?)?$/';
                if (!empty($conditions['date_after']) && preg_match($dateTimePattern, $conditions['date_after']) == 0) {
                    $errors['date_after'] = 'Date is not valid';
                }
                if (!empty($conditions['date_before']) && preg_match($dateTimePattern, $conditions['date_before']) == 0) {
                    $errors['date_before'] = 'Date is not valid';
                }

                if (empty($errors)) {
                    $jobs = $resqueStat->getJobs($conditions);
                } else {
                    $jobs = array();
                }

                $pagination->totalResult = $resqueStat->getJobs(array_merge($conditions, array('type' => 'count')));
            }


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
    '/jobs/pending',
    function () use ($app, $settings) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);

            $resultLimits = array(15, 50, 100);
            $args = $app->request()->params();

            $pagination = new stdClass();
            $pagination->current = isset($args['page']) ? $args['page'] : 1;
            $pagination->limit = (($app->request()->params('limit') != '') && in_array($app->request()->params('limit'), $resultLimits))
                ? $app->request()->params('limit')
                : PAGINATION_LIMIT;
            $pagination->baseUrl = '/jobs/pending?';

            $options = array(
                'status' => ResqueBoard\Lib\ResqueStat::JOB_STATUS_WAITING,
                'limit' => $pagination->limit,
                'queue' => null
                );
            if (isset($args['queue'])) {
                $options['queue'] = $args['queue'];
            }

            $jobsCount = $resqueStat->getPendingJobsCount($options['queue']);
            $pagination->totalResult = array_pop($jobsCount);
            $pagination->totalPage = ceil($pagination->totalResult / $pagination->limit);
            $pagination->uri = $app->request()->params();

            $app->render(
                'jobs_pending_view.ctp',
                array(
                    'jobs' => $resqueStat->getJobs($options),
                    'pageTitle' => 'Pending Jobs',
                    'queues' => $resqueStat->getQueues(),
                    'resultLimits' => $resultLimits,
                    'pagination' => $pagination
                )
            );

        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);

$app->get(
    '/jobs/overview/:range(/:start)',
    function ($range, $start = 'now') use ($app, $settings) {
        try {

            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);

            $rangeWhitelist = array(
                'hour' => array('step' => ResqueBoard\Lib\ResqueStat::CUBE_STEP_10SEC),
                'day' => array('step' => ResqueBoard\Lib\ResqueStat::CUBE_STEP_5MIN),
                'week' => array('step' => ResqueBoard\Lib\ResqueStat::CUBE_STEP_1HOUR),
                'month' => array('step' => ResqueBoard\Lib\ResqueStat::CUBE_STEP_1HOUR)
                );


            $start = new DateTime($start);

            if (!array_key_exists($range, $rangeWhitelist)) {
                throw new Exception('Invalid URL');
            }


            $rangeWhitelist = array_merge_recursive(
                $rangeWhitelist,
                array(
                    'hour' => array(
                        'start' => ResqueBoard\Lib\DateHelper::getStartHour($start),
                        'end' => ResqueBoard\Lib\DateHelper::getEndHour($start)
                    ),
                    'day' => array(
                        'start' => ResqueBoard\Lib\DateHelper::getStartDay($start),
                        'end' => ResqueBoard\Lib\DateHelper::getEndDay($start)
                    ),
                    'week' => array(
                        'start' => ResqueBoard\Lib\DateHelper::getStartWeek($start),
                        'end' =>  ResqueBoard\Lib\DateHelper::getEndWeek($start),
                    ),
                    'month' => array(
                        'start' => ResqueBoard\Lib\DateHelper::getStartMonth($start),
                        'end' => ResqueBoard\Lib\DateHelper::getEndMonth($start),
                    )
                )
            );

            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);

            $processTime = $resqueStat->getCubeMetric(
                array(
                    'start' => $rangeWhitelist[$range]['start'],
                    'end' => $rangeWhitelist[$range]['end'],
                    'expression' => 'sum(done(time))',
                    'step' => ResqueBoard\Lib\ResqueStat::CUBE_STEP_1HOUR
                )
            );
            $totalProcessTime = 0;
            foreach ($processTime as $t) {
                $totalProcessTime += $t['value'];
            }

            $jobStats = $resqueStat->getJobsStats(
                array(
                    'start' => $rangeWhitelist[$range]['start']->format('c'),
                    'end' => $rangeWhitelist[$range]['end']->format('c')
                )
            );



            $app->render(
                'jobs_overview.ctp',
                array(
                    'pageTitle' => 'Jobs overview',
                    'ranges' => $rangeWhitelist,
                    'currentRange' => $range,
                    'currentStep' => $rangeWhitelist[$range],
                    'uriDate' => $start,
                    'startDate' => $rangeWhitelist[$range]['start'],
                    'endDate' => $rangeWhitelist[$range]['end'],
                    'jobsStats' => $jobStats,
                    'totalProcessTime' => $totalProcessTime,
                    'averageProcessTime' => ($jobStats->total !== 0) ? $totalProcessTime / $jobStats->total : 0
                )
            );
        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);

$app->get(
    '/scheduled-jobs',
    function () use ($app, $settings) {
        try {

            $app->render(
                'scheduled_jobs.ctp',
                array(

                    'pageTitle' => 'Scheduled Jobs'

                )
            );

        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);

$app->map(
    '/logs/browse',
    function () use ($app, $settings, $logLevels, $logTypes) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);
            $errors  = array();

            $resultLimits = array(15, 50, 100);

            $defaults = array(
                'page' => 1,
                'limit' => $resultLimits[0],
                'event_level' => null,
                'event_type' => null,
                'date_after' => null,
                'date_before' => null
            );

            $searchData = array_merge(
                $defaults,
                $app->request()->params()
            );
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
            $pagination->baseUrl = '/logs/browse?';

            $conditions = array();

            $conditions = array(
                'page' => $searchData['page'],
                'limit' => $searchData['limit'],
                'event_level' => $searchData['event_level'],
                'event_type' => $searchData['event_type'],
                'date_after' => $searchData['date_after'],
                'date_before' => $searchData['date_before']

            );


            // Validate search datas
            $dateTimePattern = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])*?$/';
            if (!empty($conditions['date_after']) && preg_match($dateTimePattern, $conditions['date_after']) == 0) {
                $errors['date_after'] = 'Date is not valid';
            }
            if (!empty($conditions['date_before']) && preg_match($dateTimePattern, $conditions['date_before']) == 0) {
                $errors['date_before'] = 'Date is not valid';
            }


            if (empty($errors) && $app->request()->params() != array()) {
                $logs = $resqueStat->getLogs($conditions);
            } else {
                $logs = null;
            }


            $pagination->totalResult = $resqueStat->getLogs(array_merge($conditions, array('type' => 'count')));
            $pagination->totalPage = ceil($pagination->totalResult / $pagination->limit);
            $pagination->uri = $app->request()->params();

            $app->render(
                'logs_browser.ctp',
                array(
                    'logs' => $logs,
                    'resultLimits' => $resultLimits,
                    'pageTitle' => 'Logs',
                    'errors' => $errors,
                    'searchData' => $searchData,
                    'pagination' => $pagination,
                    'logLevels' => $logLevels,
                    'logTypes' => $logTypes
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
            $jobs = array_values($resqueStat->getJobs(array('date_after' => (int)$start, 'date_before' => (int)$end)));
            $app->response()->header("Content-Type", "application/json");
            echo json_encode($jobs);
        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);

$app->get(
    '/api/workers/getinfo/:workerId',
    function ($workerId) use ($app, $settings) {

        $resqueApi = new ResqueBoard\Lib\ResqueApi($settings['resqueConfig']);
        $infos = $resqueApi->getInfos($workerId);

        $app->response()->header("Content-Type", "application/json");
        echo json_encode($infos);
    }
);

$app->get(
    '/api/workers/stop/:workerId',
    function ($workerId) use ($app, $settings) {

        $app->response()->header("Content-Type", "application/json");

        if ($settings['readOnly']) {
            echo json_encode(array('status' => false, 'message' => 'You don\'t have permission to stop workers'));
            return;
        }

        $resqueApi = new ResqueBoard\Lib\ResqueApi($settings['resqueConfig']);
        $stop = $resqueApi->stop($workerId);

        echo json_encode(array('status' => true));
    }
);

$app->get(
    '/render/worker/:layout/:workerId',
    function ($layout, $workerId) use ($app, $settings) {

        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat($settings);

            switch($layout) {
                case 'list':
                    echo ResqueBoard\Lib\WorkerHelper::renderList($resqueStat->getStats(), array($resqueStat->getWorker($workerId)));
                    break;
                case 'table':
                    echo ResqueBoard\Lib\WorkerHelper::renderTable(array($resqueStat->getWorker($workerId)));
                    break;
            }


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

$app->map(
    '/api/workers/start',
    function () use ($app, $settings) {

        if ($settings['readOnly']) {
            echo json_encode(array('status' => false));
            return;
        }

        $resqueApi = new ResqueBoard\Lib\ResqueApi($settings['resqueConfig']);

        $data = array(
                'queues' => '',
                'workers' => '',
                'interval' => '',
                'user' => '',
                'log' => '',
                'include' => '',
                'host' => '',
                'port' => '',
                'database' => '',
                'namespace' => ''
            );

        $postDatas = $app->request()->params();
        $postDatas['handler'] = 'Cube';
        $postDatas['target'] = 'udp://127.0.0.1:1180';

        if ($app->request()->isPost()) {
            if ($resqueApi->start($postDatas)) {
                return json_encode(array('status' => true));
            }

            $data = $app->request()->params();
        }

        $app->render(
            'worker_form.ctp',
            array(
                'pageTitle' => 'Start a worker',
                'errors' => $resqueApi->getErrors(),
                'raw' => true,
                'data' => $data
            )
        );
    }
)->via('GET', 'POST');

$app->run();
