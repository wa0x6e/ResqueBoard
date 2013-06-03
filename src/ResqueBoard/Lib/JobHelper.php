<?php
/**
 * JobHelper file
 *
 * Help rendering a job template
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package    ResqueBoard
 * @subpackage ResqueBoard.Lib
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @copyright  2012-2013 Wan Qi Chen
 * @link       http://resqueboard.kamisama.me
 * @since      2.0.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\Lib;

/**
 * JobHelper Class
 *
 * Help rendering a job template
 *
 * @subpackage ResqueBoard.Lib
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @since      2.0.0
 */
class JobHelper
{
    public static $jobStatus = array(
        ResqueStat::JOB_STATUS_WAITING => 'waiting',
        ResqueStat::JOB_STATUS_RUNNING => 'running',
        ResqueStat::JOB_STATUS_FAILED => 'failed',
        ResqueStat::JOB_STATUS_COMPLETE => 'complete'
    );

    public static function renderJobs($jobs, $message = null, $class = null)
    {
        if (!empty($jobs)) {
            echo '<ul class="unstyled job-details ' . $class . '">';
            foreach ($jobs as $job) {
                ?>
                <li class="accordion-group<?php
                if ($job['status'] === ResqueStat::JOB_STATUS_FAILED) {
                    echo ' error';
                } ?>">
                    <div class="accordion-heading" data-toggle="collapse" data-target="#<?php echo $job['job_id']?>">
                        <div class="accordion-toggle">
                            <span title="Job <?php echo self::$jobStatus[$job['status']] ?>" class="job-status-icon">
                            <img src="img/job_<?php echo self::$jobStatus[$job['status']] ?>.png" title="Job <?php echo self::$jobStatus[$job['status']] ?>" height=24 width=24 /></span>

                            <?php
                if (isset($job['worker']) && !empty($job['worker'])) : ?>
                            <span class="label label-info pull-right"><?php echo $job['worker']?></span>
                <?php
                endif; ?>
                            <?php
                if (isset($job['time']) && !empty($job['time'])) : ?>
                            <time title="<?php echo date('c', strtotime($job['time']))?>" datetime="<?php echo date('c', strtotime($job['time']))?>"><i class="icon-time"></i> <?php echo date('H:i:s', strtotime($job['time'])); ?></time>
                            <?php
                endif; ?>

                            <h4><?php echo $job['class']?></h4>
                            <small><code>#<?php echo $job['job_id']?></code> in
                            <span class="label"><?php echo $job['queue']?></span></small>

                        </div>
                    </div>
                    <div class="collapse<?php
                if (count($jobs) === 1) {
                    echo ' in';
                } ?> accordion-body" id="<?php echo $job['job_id']?>">
                        <div class="accordion-inner">
                            <?php

                if (isset($job['log'])) {
                    echo '<div class="console"><span class="error">' . $job['log'] . '</error></div>';
                }

                if (isset($job['trace'])) {
                    echo '<h5>Error trace</h5>';
                    echo '<pre class="job-trace"><code class="language-php">'. $job['trace'] . '</code></pre>';
                }
                            ?>

                            <h5>Job arguments</h5>
                            <pre class="job-args"><code class="language-php"><?php echo $job['args'] ?></code></pre>
                        </div>
                    </div>
                </li>
            <?php
            }
            echo '</ul>';
        } elseif ($message !== null) {
            echo '<div class="alert">'. $message .'</div>';
        }
    }
}
