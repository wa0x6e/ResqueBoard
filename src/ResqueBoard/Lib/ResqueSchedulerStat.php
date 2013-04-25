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
            $end = $this->getRedis()->zRevRange(
                $this->settings['resquePrefix'] . \ResqueScheduler\ResqueScheduler::QUEUE_NAME,
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

        $timestamps = $this->getRedis()->zrangebyscore($this->settings['resquePrefix'] . \ResqueScheduler\ResqueScheduler::QUEUE_NAME, $start, $end);
        if (empty($timestamps)) {
            return 0;
        }

        $redisPipeline = $this->getRedis()->multi(\Redis::PIPELINE);
        foreach ($timestamps as $key) {
            $redisPipeline->llen($this->settings['resquePrefix'] . \ResqueScheduler\ResqueScheduler::QUEUE_NAME . ':' . $key);
        }
        $timestampLength = $redisPipeline->exec();

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
            $end = (int) $this->getRedis()->zcard($this->settings['resquePrefix'] . \ResqueScheduler\ResqueScheduler::QUEUE_NAME);
        }

        $timestamps = $this->getRedis()->zrangebyscore($this->settings['resquePrefix'] . \ResqueScheduler\ResqueScheduler::QUEUE_NAME, $start, $end);

        if (empty($timestamps)) {
            return array();
        }

        $redisPipeline = $this->getRedis()->multi(\Redis::PIPELINE);
        foreach ($timestamps as $key) {
            $redisPipeline->llen($this->settings['resquePrefix'] . \ResqueScheduler\ResqueScheduler::QUEUE_NAME . ':' . $key);
        }
        $timestampLength = $redisPipeline->exec();

        $i = 0;
        $redisPipeline = $this->getRedis()->multi(\Redis::PIPELINE);
        foreach ($timestamps as $key) {
            $redisPipeline->lrange($this->settings['resquePrefix'] . \ResqueScheduler\ResqueScheduler::QUEUE_NAME . ':' . $key, 0, (int)$timestampLength[$i++]);
        }
        $jobs = $redisPipeline->exec();

        foreach ($jobs as &$job) {
            foreach ($job as &$j) {
                $j = json_decode($j, true);
                $j['id'] = $j['args'][0]['id'];
                unset($j['args'][0]['id']);
                $j['args'] = $j['args'][0];
            }
        }

        return array_combine($timestamps, $jobs);
    }
}
