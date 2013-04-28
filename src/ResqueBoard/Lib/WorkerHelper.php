<?php
/**
 * WorkerHelper file
 *
 * Help rendering a worker template
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
 * @since         1.2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\Lib;

/**
 * WorkerHelper Class
 *
 * Help rendering a worker template
 *
 * @author Wan Qi Chen <kami@kamisama.me>
 */
class WorkerHelper
{
    public static function renderList($stats, $workers = array(), $readOnly = true)
    {
        if (!empty($workers)) {
            foreach ($workers as $worker) {
                $workerId = str_replace('.', '', $worker['host']) . $worker['process'];
                ?>

                    <div class="workers-list-item" id="<?php echo $workerId ?>">

                            <div class="worker-list-inner">

                                <div class="chart">
                                    <div class="chart-pie" data-type="chart" data-chart-type="pie" data-processed="<?php
                                        echo $worker['processed'] - $worker['failed']?>"
                                        data-failed="<?php echo $worker['failed'] ?>">
                                    </div>

                                    <small><b>
                                    <?php
                                        $start = $worker['start'];
                                        $diff = $start->diff(new \DateTime());

                                        $minDiff = $diff->i + $diff->h*60 + $diff->d*24*60 + $diff->m*30*24*60 + $diff->y*365*30*24*60 ;

                                        echo $minDiff == 0 ? 0 : round($worker['processed'] / $minDiff, 2);

                                    ?></b> jobs / min</small>

                                </div>

                                <div class="worker-list-inner-data">

                                    <h3><?php echo $worker['host']?>:<?php echo $worker['process']; ?></h3>

                                    <small><strong><i class="icon-list-alt"></i> Queues : </strong><?php
                                                                        array_walk(
                                                                            $worker['queues'],
                                                                            function ($q) {
                                                                                echo '<span class="queue-name">'.$q.'</span> ';
                                                                            }
                                                                        )?></small>

                                    <div class="worker-stats clearfix">
                                        <div class="stat-count">
                                            <a href="">
                                            <b data-status="processed"><?php echo number_format($worker['processed'])?></b>
                                            Processed</a>
                                        </div>
                                        <div class="stat-count">
                                            <a href="">
                                            <b class="warning" data-status="failed"><?php echo number_format($worker['failed'])?></b>
                                            Failed</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <ul class="worker-list-footer">

                            <li>
                                <a href="#" class="stop-worker" data-worker-id="<?php echo $worker['fullname'] ?>" data-worker-name="<?php echo $workerId ?>" title="Stop worker">
                                    <i class="icon-off"></i> Stop
                                </a>
                            </li>
                            <li>
                                <a href="#" class="pause-worker" data-worker-id="<?php echo $worker['fullname'] ?>" data-worker-name="<?php echo $workerId ?>" title="Pause worker">
                                    <i class="icon-pause"></i> Pause
                                </a>
                            </li>
                             <li class="text"><i class="icon-time"></i> <strong>Uptime</strong> <time datetime="<?php echo date_format($worker['start'], "c")?>" title="Started on <?php echo date_format($worker['start'], "r")?>">
                            <?php echo DateHelper::ago($worker['start'])?></time></li>
                        </ul>

                </div>

                <?php
            }
        }
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

    public static function renderTemplate()
    {
        ?>
        <div ng-controller="WorkersCtrl" ng-cloak>

            <div id="worker-form" class="modal hide"></div>
            <div id="worker-details" class="modal hide">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h3>Worker properties</h3>
                </div>
                <ul class="modal-body unstyled">
                </ul>
            </div>

            <h2>Workers <span class="badge badge-info">{{length}}</span></h2>
            <div class="workers-list">
                <div class="workers-list-item" ng-repeat="worker in workers" ng-class="{paused: !worker.active}">
                    <div class="worker-list-inner">
                        <div class="chart">
                           <!-- <div class="chart-pie" data-type="chart" data-chart-type="pie" data-processed="<?php
                               // echo $worker['processed'] - $worker['failed']?>"
                                data-failed="<?php //echo $worker['failed'] ?>">
                            </div>-->

                            <small><b>{{worker.stats.jobrate|number:0}}</b> jobs / min</small>

                        </div>

                        <div class="worker-list-inner-data">

                            <span class="pull-right badge" ng-hide="worker.active">paused</span>
                            <h3>{{worker.host}}:{{worker.process}}</h3>

                            <small><strong><i class="icon-list-alt"></i> Queues : </strong>
                                <span class="queue-name" ng-repeat="queue in worker.queues">{{queue}}</span>
                            </small>

                            <div class="worker-stats clearfix">
                                <div class="stat-count">
                                    <a href="/jobs/view?worker[]={{worker.host}}%3A{{worker.process}}">
                                    <b>{{worker.stats.processed|number}}</b>
                                    Processed</a>
                                </div>
                                <div class="stat-count">
                                    <a href="/jobs/view?worker[]={{worker.host}}%3A{{worker.process}}">
                                    <b class="warning" >{{worker.stats.failed|number}}</b>
                                    Failed</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul class="worker-list-footer">
                        <li>
                            <a href="#" title="Stop worker" ng-click="stop($index)">
                                <i class="icon-off"></i> Stop
                            </a>
                        </li>
                        <li>
                            <a href="#" ng-show="worker.active" title="Pause worker" ng-click="pause($index)">
                                <i class="icon-pause"></i> Pause
                            </a>
                        </li>
                        <li>
                            <a href="#" ng-hide="worker.active" title="Resume worker" ng-click="resume($index)">
                                <i class="icon-play"></i> Resume
                            </a>
                        </li>
                        <li class="text">
                            <i class="icon-time"></i>
                            <strong>Uptime</strong>
                            <time datetime="{{worker.start}}" title="Started on {{worker.start}}">
                            {{worker.start|uptime}}</time></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
}
