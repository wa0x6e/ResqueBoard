<?php
/**
 * SchedulerWorkerHelper file
 *
 * Help rendering a scheduler worker template
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
 * @subpackage    resqueboard.lib
 * @since         1.5.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\Lib;

/**
 * SchedulerWorkerHelper Class
 *
 * Help rendering a scheduler worker template
 *
 * @author Wan Qi Chen <kami@kamisama.me>
 */
class SchedulerWorkerHelper extends WorkerHelper
{
    public static function renderList($totalStats, $workers = array(), $readOnly = true)
    {
        ?>
        <div class="span4">
                <div class="workers-list-item">
                    <h3 class="sub">Total Scheduled jobs stats</h3>
                    <div class="worker-list-inner">
                        <div class="worker-stats clearfix">
                            <div class="stat-count">
                                <b data-status="total"><?php echo number_format($totalStats['total']['scheduled'])?></b>
                            Total scheduled
                            </div>
                    </div>

                     <?php
                        if (!empty($workers)) {
                            $worker = $workers[0];
                            $workerId = str_replace('.', '', $worker['host']) . $worker['process'];

                            echo '<div id="' . $workerId . '">';

                            if (!$readOnly) : ?>
                            <div class="btn-group pull-right">
                                <a class="btn btn-small dropdown-toggle btn-discret" data-toggle="dropdown" href="#">
                                    <i class="icon-cog"></i>
                                    </a>
                                <ul class="dropdown-menu">
                                <li><a href="#" class="stop-worker" data-worker-id="<?php echo $worker['fullname'] ?>" data-worker-name="<?php echo $workerId ?>"><i class="icon-off"></i> Stop worker</a></li>
                                <!-- <li><a href="#" class="get-worker-info" data-worker-id="<?php echo $worker['fullname'] ?>">View properties</a></li> -->
                                </ul>
                            </div>
                            <?php endif; ?>

                                <h3 class="sub">Scheduled jobs stats</h3>
                                <div class="worker-list-inner">
                                    <div class="worker-stats clearfix" id="<?php echo $workerId?>">

                                        <div class="stat-count">
                                            <b data-status="scheduled"><?php echo number_format($worker['scheduled'])?></b>
                                        Currently waiting
                                        </div>
                                </div>
                            </div>

                            <?php
                        } else {
                            echo '<h3 class="sub">Scheduler worker</h3>';
                            echo '<div class="alert alert-error"><i class="icon-exclamation-sign"></i> The scheduler worker is not running</div>';
                        }
                    ?>
                </div>
            </div>
        </div>
        <?php

    }

    public static function renderTable($workers = array(), $readOnly = true)
    {
        $totalJobs = 0;
        $i = 0;
        array_walk(
            $workers,
            function ($q) use (&$totalJobs) {
                $totalJobs += $q['processed'];
            }
        );

        foreach ($workers as $worker) {
            // Skip the Scheduler Worker
            if (implode('', $worker['queues']) === \ResqueScheduler\ResqueScheduler::QUEUE_NAME) {
                continue;
            }

            $barWidth = $totalJobs != 0 ? (($worker['processed']/$totalJobs) * 100)  : 0;

            $workerId = str_replace('.', '', $worker['host']) . $worker['process'];
            echo '<tr class="worker-stats" id="'.$workerId.'">';
            echo '<td>';

            if (!$readOnly) : ?>
            <div class="btn-group pull-right">
                <a class="btn btn-small dropdown-toggle btn-discret" data-toggle="dropdown" href="#">
                    <i class="icon-cog"></i>
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a href="#" class="stop-worker" data-worker-id="<?php echo $worker['fullname'] ?>" data-worker-name="<?php echo $workerId ?>">
                        <i class="icon-off"></i> Stop worker</a>
                    </li>
                </ul>
            </div>
            <?php
            endif;
            echo'<h4>' . $worker['host'] . ':' . $worker['process']. '</h4>';
            echo '<small class="queues-list"><strong><i class="icon-list-alt"></i> Queues : </strong>';
            array_walk(
                $worker['queues'],
                function ($q) {
                    echo '<span class="queue-name">'.$q.'</span> ';
                }
            );
            echo '</small></td>';
            echo '<td class="stats-number inner-wrap"><div class="padd-fixer">' .
            '<span class="chart-bar" data-type="chart" data-chart-type="horizontal-bar" style="width:'.$barWidth.'%;"></span>'.
            '<b data-status="processed">'.number_format($worker['processed']) . '</b></div></td>';
            echo '<td class="stats-number inner-wrap"><div class="padd-fixer"><b data-status="failed">'.number_format($worker['failed']) . '</b></div></td>';
            echo '<td class="inner-wrap">';
            if ($i++ === 0) {
                echo '<div class="padd-fixer"><div id="worker-activities"></div></div>';
            }
            echo '</td>';
            echo '</tr>';
        }
    }
}
