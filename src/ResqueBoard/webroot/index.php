<?php
/**
 * Index file
 *
 * Application entry point, defines routes
 *
 * PHP version 5
 *
 * Licensed under The MIT Lice€nse
 * Redistributions of files must retain the above copyright notice.
 *
 * @package    ResqueBoard
 * @subpackage ResqueBoard
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @copyright  2012-2013 Wan Qi Chen
 * @link       http://resqueboard.kamisama.me
 * @since      1.0.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

if (!defined('ROOT')) {
    define('ROOT', dirname(dirname(__FILE__)));
}

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

define('APPLICATION_VERSION', '2.0.0-beta-3');

include ROOT . DS . 'Config' . DS . 'Bootstrap.php';

$app = new Slim\Slim($config);

$app->runtime = $settings;

$app->get(
    '/',
    function () use ($app) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat();

            render(
                $app,
                'index',
                array(
                    'stats' => $resqueStat->getStats()
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

        render(
            $app,
            'logs',
            array(
                'logLevels' => $logLevels,
                'logTypes' => $logTypes,
                'mutedLevels' => $mutedLevels,
            )
        );
    }
);

$app->get(
    '/workers',
    function () use ($app) {
        try {
            render($app, 'workers');
        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);

$app->get(
    '/jobs',
    function () use ($app) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat();

            render(
                $app,
                'jobs',
                array(
                    'stats' => $resqueStat->getStats()
                )
            );

        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);

$app->get(
    '/jobs/distribution/class',
    function () use ($app) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat();

            render(
                $app,
                'jobs_class_distribution',
                array(
                    'jobsRepartitionStats' => $resqueStat->getJobsRepartionStats(null)
                )
            );

        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);

$app->get(
    '/jobs/distribution/load(/:year/:month)',
    function ($year = null, $month = null) use ($app) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat();

            if ($year === null && $month === null) {
                $start = new \DateTime();
                $start->modify('first day of this month');
                $start->setTime(0, 0, 0);
            } else {
                $start = new \DateTime($year . '-' . $month . '-01');
            }

            $cacheId = $start->format('Y-m');
            $cacheDriver = new \Doctrine\Common\Cache\FilesystemCache(CACHE . DS . 'jobs' . DS . 'load-distribution', '.cache');

            if ($cacheDriver->contains($cacheId)) {
                $jobsMatrix = $cacheDriver->fetch($cacheId);
            } else {
                $end = clone $start;
                $end->modify('last day of ' . $end->format('F') . ' ' . $end->format('Y'));
                $end->setTime(23, 59, 59);
                $jobsMatrix = $resqueStat->getJobsMatrix($start, $end, ResqueBoard\Lib\ResqueStat::CUBE_STEP_1DAY);

                $now = new \DateTime();
                if ($start->format('Ym') < $now->format('Ym')) {
                    $cacheDriver->save($cacheId, $jobsMatrix);
                }
            }

            $firstJob = $resqueStat->getJobs(array('limit' => 1, 'sort' => array('t' => 1), 'format' => false));
            if (!empty($firstJob)) {
                $startDate = current($firstJob);
                $startDate = new \DateTime($startDate['time']);
            } else {
                $startDate = new \DateTime();
            }

            render(
                $app,
                'jobs_load_distribution',
                array(
                    'jobsMatrix' => $jobsMatrix,
                    'currentDate' => $start,
                    'start' => $startDate
                )
            );

        } catch (\Exception $e) {
            $app->error($e);
        }
    }
)->conditions(array('year' => '(19|20)\d\d', 'month' => '(0\d|1[0-2])'));

$app->map(
    '/jobs/view',
    function () use ($app) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat();
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

            $params = cleanArgs($app->request()->params());

            $searchData = array_merge($defaults, $params);
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
            $pagination->limit = (isset($params['limit']) && in_array($params['limit'], $resultLimits))
                ? $params['limit']
                : PAGINATION_LIMIT;
            $pagination->baseUrl = 'jobs/view?';

            $conditions = array();
            $searchToken = '';

            $pagination->totalResult = $resqueStat->getJobs(array_merge($conditions, array('type' => 'count')));

            if (isset($params['job_id'])) {
                $jobId = $searchToken = ltrim($params['job_id'], '#');
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
            $pagination->uri = $params;

            render(
                $app,
                'jobs_view_processed',
                array(
                    'jobs' => $jobs,
                    'workers' => $activeWorkers,
                    'resultLimits' => $resultLimits,
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
    function () use ($app) {
        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat();

            $resultLimits = array(15, 50, 100);
            $args = cleanArgs($app->request()->params());

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

            $pagination->totalResult = array_sum($resqueStat->getPendingJobsCount($options['queue']));
            $pagination->totalPage = ceil($pagination->totalResult / $pagination->limit);
            $pagination->uri = cleanArgs($app->request()->params());

            render(
                $app,
                'jobs_view_pending',
                array(
                    'jobs' => $resqueStat->getJobs($options),
                    'queues' => $resqueStat->getQueues(array('pendingjobs')),
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
    function ($range, $start = 'now') use ($app) {
        try {

            $resqueStat = new ResqueBoard\Lib\ResqueStat();

            $rangeWhitelist = array(
                'hour' => array('step' => ResqueBoard\Lib\ResqueStat::CUBE_STEP_10SEC),
                'day' => array('step' => ResqueBoard\Lib\ResqueStat::CUBE_STEP_5MIN),
                'week' => array('step' => ResqueBoard\Lib\ResqueStat::CUBE_STEP_1HOUR),
                'month' => array('step' => ResqueBoard\Lib\ResqueStat::CUBE_STEP_1HOUR)
                );

            $start = new DateTime($start);

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
                    'end' => $rangeWhitelist[$range]['end']->format('c'),
                    'fields' => array(
                        'total',
                        ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED,
                        ResqueBoard\Lib\ResqueStat::JOB_STATUS_SCHEDULED
                    )
                )
            );

            render(
                $app,
                'jobs_load_overview',
                array(
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
)->conditions(array('range' => '(hour|day|week|month)'));

$app->get(
    '/jobs/scheduled',
    function () use ($app) {
        try {
            render(
                $app,
                'jobs_view_scheduled',
                array(
                    'ngController' => 'scheduledJobController'
                )
            );

        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);

$app->map(
    '/logs/browse',
    function () use ($app, $logLevels, $logTypes) {
        try {

            $errors  = array();

            $resultLimits = array(25, 50, 100);

            $defaults = array(
                'page' => 1,
                'limit' => $resultLimits[0],
                'event_level' => null,
                'event_type' => null,
                'date_after' => null,
                'date_before' => null
            );

            $params = cleanArgs($app->request()->params());

            $searchData = array_merge(
                $defaults,
                $params
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
            $pagination->limit = (isset($params['limit']) && in_array($params['limit'], $resultLimits))
            ? $params['limit']
            : PAGINATION_LIMIT;
            $pagination->baseUrl = 'logs/browse?';

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
            $dateTimePattern = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])(\s+([0-1][0-9]|2[0-4]):[0-5]\d(:[0-5]\d)?)?$/';
            if (!empty($conditions['date_after']) && preg_match($dateTimePattern, $conditions['date_after']) == 0) {
                $errors['date_after'] = 'Date is not valid';
            }
            if (!empty($conditions['date_before']) && preg_match($dateTimePattern, $conditions['date_before']) == 0) {
                $errors['date_before'] = 'Date is not valid';
            }

            if (empty($errors) && $params != array()) {
                $resqueStat = new ResqueBoard\Lib\ResqueStat();
                $logs = $resqueStat->getLogs($conditions);
                $pagination->totalResult = $resqueStat->getLogs(array_merge($conditions, array('type' => 'count')));
            } else {
                $logs = null;
                $pagination->totalResult = 0;
            }

            $pagination->totalPage = ceil($pagination->totalResult / $pagination->limit);
            $pagination->uri = $params;

            render(
                $app,
                'logs_browser',
                array(
                    'logs' => $logs,
                    'resultLimits' => $resultLimits,
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
)->via('GET');

/**
 * Return all the jobs between a start and end date
 * @param  int $start   Start timestamp
 * @param  int $end     End timestamp
 */
$app->get(
    '/api/jobs/:start/:end',
    function ($start, $end) use ($app) {
        $app->response()->header("Content-Type", "application/json");

        try {
            $cacheId = $start.$end;
            $cacheDriver = new \Doctrine\Common\Cache\FilesystemCache(CACHE . DS . 'jobs' . DS . 'list', '.cache');

            if ($cacheDriver->contains($cacheId)) {
                $jobs = $cacheDriver->fetch($cacheId);
            } else {
                $resqueStat = new ResqueBoard\Lib\ResqueStat();
                $jobs = array_values($resqueStat->getJobs(array('date_after' => (int)$start, 'date_before' => (int)$end)));
                $jobs = json_encode($jobs);

                if ($start < $end && $end < time()) {
                    $cacheDriver->save($cacheId, $jobs);
                }
            }
            echo $jobs;
        } catch (\Exception $e) {
            $app->error($e);
        }
    }
)->conditions(array('start' => '\d+', 'end' => '\d+'));


/**
 * Return a list of jobs count grouped by time
 * Used to populate the cal-heatmap
 */
$app->get(
    '/api/jobs/stats/:start/:end',
    function ($start, $end) use ($app) {
        $app->response()->header("Content-Type", "application/json");

        try {
            $cacheId = $start.$end;
            $cacheDriver = new \Doctrine\Common\Cache\FilesystemCache(CACHE . DS . 'jobs' . DS . 'stats', '.cache');

            if ($cacheDriver->contains($cacheId)) {
                $jobs = $cacheDriver->fetch($cacheId);
            } else {
                $resqueStat = new ResqueBoard\Lib\ResqueStat();
                $jobs = $resqueStat->getJobsCount(array('date_after' => (int)$start, 'date_before' => (int)$end+60));
                $jobs = json_encode($jobs);

                if ($start < $end && $end < time()) {
                    $cacheDriver->save($cacheId, $jobs);
                }
            }
            echo $jobs;
        } catch (\Exception $e) {
            $app->error($e);
        }
    }
)->conditions(array('start' => '\d+', 'end' => '\d+'));

$app->get(
    '/api/jobs/distribution/class(/:limit)',
    function ($limit = null) use ($app) {
        $app->response()->header("Content-Type", "application/json");

        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat();

            $app->view(new ResqueBoard\View\JsonView());
            $app->render(
                'jobs_class_distribution.ctp',
                array(
                    'class' => $resqueStat->getJobsRepartionStats($limit)
                )
            );

        } catch (\Exception $e) {
            $app->error($e);
        }
    }
)->conditions(array('limit' => '\d+'));

/**
 * Return a list of scheduled jobs count grouped by time
 * Used to populate the cal-heatmap
 */
$app->get(
    '/api/scheduled-jobs/stats/:start/:end',
    function ($start, $end) use ($app) {
        $app->response()->header("Content-Type", "application/json");

        try {
            $cacheId = $start.$end;
            $cacheDriver = new \Doctrine\Common\Cache\FilesystemCache(CACHE . DS . 'scheduled-jobs' . DS . 'stats', '.cache');

            if ($cacheDriver->contains($cacheId)) {
                $jobs = $cacheDriver->fetch($cacheId);
            } else {
                $resqueSchedulerStat = new ResqueBoard\Lib\ResqueSchedulerStat();
                $jobs = $resqueSchedulerStat->getScheduledJobsCount((int)$start, (int)$end+60, true);
                $jobs = json_encode($jobs);

                if ($start < $end && $end < time()) {
                    $cacheDriver->save($cacheId, $jobs);
                }
            }

            echo $jobs;
        } catch (\Exception $e) {
            $app->error($e);
        }
    }
)->conditions(array('start' => '\d+', 'end' => '\d+'));

$app->get(
    '/api/scheduled-jobs/:start/:end',
    function ($start, $end) use ($app) {
        $app->response()->header("Content-Type", "application/json");

        try {
            $resqueSchedulerStat = new ResqueBoard\Lib\ResqueSchedulerStat();
            $jobs = $resqueSchedulerStat->getJobs((int)$start, (int)$end, true);
            echo json_encode($jobs);
        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);

$app->get(
    '/api/stats',
    function () use ($app) {
        $app->response()->header("Content-Type", "application/json");

        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat();
            $resqueSchedulerStat = new ResqueBoard\Lib\ResqueSchedulerStat();

            $args = cleanArgs($app->request()->params());
            $stats = array();

            if (isset($args['fields'])) {
                if (in_array('scheduled_full', explode(',', $args['fields']))) {
                    $stats['scheduled'] = array(
                        'total' => $resqueSchedulerStat->getStats(ResqueBoard\Lib\ResqueStat::JOB_STATUS_SCHEDULED),
                        'future' => $resqueSchedulerStat->getScheduledJobsCount(time()),
                        'past' => $resqueSchedulerStat->getScheduledJobsCount(0, time())
                    );
                }
                if (in_array('scheduled', explode(',', $args['fields']))) {
                    $stats['scheduled'] = array(
                        'total' => $resqueSchedulerStat->getStats(ResqueBoard\Lib\ResqueStat::JOB_STATUS_SCHEDULED)
                    );
                }
                if (in_array('pending_full', explode(',', $args['fields']))) {
                    $queuesStats = $resqueStat->getPendingJobsCount(explode(',', $args['queues']));

                    $results = array();
                    $total = 0;
                    foreach ($queuesStats as $name => $count) {
                        $total += $count;
                        $results[$name] = $count;
                    }

                    $stats['pending'] = array(
                        'total' => $total,
                        'queues' => $results
                    );
                }
                if (in_array('pending', explode(',', $args['fields']))) {
                    $stats['pending']['total'] = 0;
                    $r = $resqueStat->getPendingJobsCount();
                    array_walk(
                        $r,
                        function ($q) use (&$stats) {
                            $stats['pending']['total'] += $q;
                        }
                    );

                }
            }

            echo json_encode($stats);
        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);

/**
 * Return a list of all active workers
 */
$app->get(
    '/api/workers',
    function () use ($app) {
        $app->response()->header("Content-Type", "application/json");

        try {
            $resqueStat = new ResqueBoard\Lib\ResqueStat();
            $workers = $resqueStat->getWorkers();

            $results = array();

            foreach ($workers as $key => $value) {

                $id = $value['host'] . ':' . $value['process'];

                $results[$id] = $value;
                $results[$id]['id'] = $id;

                $results[$id]['start'] = $results[$id]['start']->format('c');
                $results[$id]['stats'] = array(
                    'processed' => $results[$id]['processed'],
                    'failed' => $results[$id]['failed']
                );

                unset($results[$id]['processed'], $results[$id]['failed']);
            }

            echo json_encode($results);
        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);

$app->get(
    '/api/workers/getinfo/:workerId',
    function ($workerId) use ($app) {
        $app->response()->header("Content-Type", "application/json");

        $resqueApi = new ResqueBoard\Lib\ResqueApi($settings['resqueConfig']);
        $infos = $resqueApi->getInfos($workerId);
        echo json_encode($infos);
    }
);

$app->get(
    '/api/workers/stop/:worker',
    function ($worker) use ($app, $settings) {

        $app->response()->header("Content-Type", "application/json");

        if ($settings['readOnly']) {
            $app->response()->header("Status", "403");
            echo json_encode(array('message' => 'You don\'t have permission to stop worker'));
            return;
        }

        try {
            $resqueApi = new ResqueBoard\Lib\Resque\Api(ResqueBoard\Lib\Service\Service::Redis());
            $response = $resqueApi->stop($worker);

            if ($response !== true) {
                echo json_encode(array('message' => $response));
            }
        } catch (ResqueBoard\Lib\Resque\InvalidWorkerNameException $e) {
            $app->response()->header("Status", "400");
            echo json_encode(array('message' => 'Invalid worker name'));
        } catch (ResqueBoard\Lib\Resque\WorkerNotExistException $e) {
            $app->response()->header("Status", "410");
            echo json_encode(array('message' => 'Worker not found'));
        }
    }
);

$app->get(
    '/api/workers/pause/:worker',
    function ($worker) use ($app, $settings) {

        $app->response()->header("Content-Type", "application/json");

        if ($settings['readOnly']) {
            $app->response()->header("Status", "403");
            echo json_encode(array('message' => 'You don\'t have permission to pause worker'));
            return;
        }

        try {
            $resqueApi = new ResqueBoard\Lib\Resque\Api(ResqueBoard\Lib\Service\Service::Redis());
            $response = $resqueApi->pause($worker);

            if ($response !== true) {
                echo json_encode(array('message' => $response));
            }
        } catch (ResqueBoard\Lib\Resque\InvalidWorkerNameException $e) {
            $app->response()->header("Status", "400");
            echo json_encode(array('message' => 'Invalid worker name'));
        } catch (ResqueBoard\Lib\Resque\WorkerNotExistException $e) {
            $app->response()->header("Status", "410");
            echo json_encode(array('message' => 'Worker not found'));
        } catch (ResqueBoard\Lib\Resque\WorkerAlreadyPausedException $e) {
            $app->response()->header("Status", "404");
            echo json_encode(array('message' => 'Worker is already paused'));
        }
    }
);

$app->get(
    '/api/workers/resume/:worker',
    function ($worker) use ($app, $settings) {

        $app->response()->header("Content-Type", "application/json");

        if ($settings['readOnly']) {
            $app->response()->header("Status", "403");
            echo json_encode(array('message' => 'You don\'t have permission to resume worker'));
            return;
        }

        try {
            $resqueApi = new ResqueBoard\Lib\Resque\Api(ResqueBoard\Lib\Service\Service::Redis());
            $response = $resqueApi->resume($worker);

            if ($response !== true) {
                echo json_encode(array('message' => $response));
            }
        } catch (ResqueBoard\Lib\Resque\InvalidWorkerNameException $e) {
            $app->response()->header("Status", "400");
            echo json_encode(array('message' => 'Invalid worker name'));
        } catch (ResqueBoard\Lib\Resque\WorkerNotExistException $e) {
            $app->response()->header("Status", "410");
            echo json_encode(array('message' => 'Worker not found'));
        } catch (ResqueBoard\Lib\Resque\WorkerNotPausedException $e) {
            $app->response()->header("Status", "404");
            echo json_encode(array('message' => 'Worker is already running'));
        }
    }
);

$app->get(
    '/api/queues',
    function () use ($app) {
        $app->response()->header("Content-Type", "application/json");

        try {
            $params = cleanArgs($app->request()->params());
            $fields = isset($params['fields']) ? explode(',', $params['fields']) : array();
            $queues = isset($params['queues']) ? explode(',', $params['queues']) : array();

            $resqueStat = new ResqueBoard\Lib\ResqueStat();

            echo json_encode(array_values($resqueStat->getQueues($fields, $queues)));
        } catch (\Exception $e) {
            $app->error($e);
        }
    }
);


$app->error(
    function (\Exception $e) use ($app) {
        $res = $app->response();
        if (isset($res['Content-Type']) && $res['Content-Type'] === 'application/json') {
            $app->view(new ResqueBoard\View\JsonView());
        }
        $app->render(
            'error.ctp',
            array(
                'pageTitle' => 'Error',
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            )
        );
    }
);

$app->notFound(
    function () use ($app) {
        $app->render(
            'error404.ctp',
            array(
                'pageTitle' => '404 Page not found',
                'message' => 'The page you\'re looking for does not exist'
            )
        );
    }
);

$app->map(
    '/api/workers/start',
    function () use ($app) {

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

        $postDatas = cleanArgs($app->request()->params());
        $postDatas['handler'] = 'Cube';
        $postDatas['target'] = 'udp://127.0.0.1:1180';

        if ($app->request()->isPost()) {
            if ($resqueApi->start($postDatas)) {
                return json_encode(array('status' => true));
            }

            $data = cleanArgs($app->request()->params());
        }

        render(
            $app,
            'worker_form',
            array(
                'errors' => $resqueApi->getErrors(),
                'raw' => true,
                'data' => $data
            )
        );
    }
)->via('GET', 'POST');

$app->run();

function cleanArgs($args)
{
    if (isset($args[0])) {
        $args[0] = parse_url($args[0], PHP_URL_QUERY);
    }
    return $args;
}

function render($app, $template, $args = array())
{
    $args['navs'] = $app->runtime['nav'];
    $args['readOnly'] = $app->runtime['readOnly'];

    $args['current'] = getMenu($args['navs'], $template);

    if (!isset($args['pageTitle'])) {
        $args['pageTitle'] = $args['current']['title'];
    }

    $app->render($template . '.ctp', $args);
}

function getMenu($args, $index)
{
    if (strpos($index, '_') !== false) {
        $args = $args[substr($index, 0, strpos($index, '_'))]['submenu'];
        $index = substr($index, strpos($index, '_') + 1);
    }

    return $args[$index];
}
