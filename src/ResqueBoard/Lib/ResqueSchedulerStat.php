<?php
/**
 * ResqueStat class
 *
 * Fetch various informations about scheduled jobs
 *
 * @package         resqueboard
 * @subpackage      resqueboard.lib
 * @since           1.5.0
 * @author          Wan Qi Chen <kami@kamisama.me>
 */

namespace ResqueBoard\Lib;

use ResqueBoard\Lib\Service\Service;

class ResqueSchedulerStat extends ResqueStat
{
    /**
     * Return the number of scheduled jobs between a start and end date
     *
     * @param  integer $start Start timestamp
     * @param  integer $end   Stop timestamp
     * @return integer        Number of scheduled jobs from that date ragne
     */
    public function getScheduledJobsCount($start = 0, $end = null, $full = false)
    {
        if ($end === null) {
            $end = Service::Redis()->zRevRange(
                \ResqueScheduler\ResqueScheduler::QUEUE_NAME,
                0,
                0
            );

            if (!is_array($end) || empty($end)) {
                return 0;
            }

            $end = (int)array_pop($end);

        }

        if ($start > $end) {
            return 0;
        }

        $timestamps = Service::Redis()->zrangebyscore(\ResqueScheduler\ResqueScheduler::QUEUE_NAME, $start, $end);
        if (empty($timestamps)) {
            return 0;
        }

        $pipelineCommands = array();
        foreach ($timestamps as $key) {
            $pipelineCommands[] = array('llen', \ResqueScheduler\ResqueScheduler::QUEUE_NAME . ':' . $key);
        }
        $timestampLength = Service::Redis()->pipeline($pipelineCommands, \Redis::PIPELINE);

        if ($full) {
            return array_combine($timestamps, $timestampLength);
        }

        return array_sum($timestampLength);
    }


    /**
     * Return an array of scheduled jobs
     *
     * @param  string $start timestamp
     * @param  string $end   timestamp
     * @return array         array of jobs
     */
    public function getJobs($start = 0, $end = null)
    {

        if ($end === null) {
            $end = (int) Service::Redis()->zcard(\ResqueScheduler\ResqueScheduler::QUEUE_NAME);
        }

        $timestamps = Service::Redis()->zrangebyscore(\ResqueScheduler\ResqueScheduler::QUEUE_NAME, $start, $end);

        if (empty($timestamps)) {
            return array();
        }

        $pipelineCommands = array();
        foreach ($timestamps as $key) {
            $pipelineCommands[] = array('llen', \ResqueScheduler\ResqueScheduler::QUEUE_NAME . ':' . $key);
        }
        $timestampLength = Service::Redis()->pipeline($pipelineCommands, \Redis::PIPELINE);

        $i = 0;
        $pipelineCommands = array();
        foreach ($timestamps as $key) {
            $pipelineCommands[] = array('lrange', array(\ResqueScheduler\ResqueScheduler::QUEUE_NAME . ':' . $key, 0, (int)$timestampLength[$i++]));
        }
        $jobs = Service::Redis()->pipeline($pipelineCommands, \Redis::PIPELINE);

        $results = array();

        $i = 0;
        foreach ($jobs as &$job) {
            foreach ($job as &$j) {
                $j = json_decode($j, true);
                $j['job_id'] = $j['args'][0]['id'];
                unset($j['args'][0]['id']);
                $j['args'] = var_export($j['args'][0], true);
                $j['time'] = date('c', $timestamps[$i]);
                $j['status'] = self::JOB_STATUS_SCHEDULED;

                $results[] = $j;
            }
            $i++;
        }
        return $results;
    }
}
