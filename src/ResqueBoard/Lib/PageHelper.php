<?php
/**
 * PageHelper file
 *
 * Various global helpers functions
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        Wan Qi Chen <kami@kamisama.me>
 * @copyright     Copyright 2013, Wan Qi Chen <kami@kamisama.me>
 * @link          http://resqueboard.kamisama.me
 * @package       resqueboard
 * @subpackage    resqueboard.lib
 * @since         2.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\Lib;

/**
 * PageHelper Class
 *
 * Various global helpers functions
 *
 * @author Wan Qi Chen <kami@kamisama.me>
 */
class PageHelper
{
    public static function renderPagination($pagination)
    {
        if (isset($pagination)) {
            ?>
            <ul class="pager">
            <li class="previous<?php
            if ($pagination->current == 1) {
                echo ' disabled';
            } ?>">
                <a href="<?php
            if ($pagination->current > 1) {
                echo $pagination->baseUrl . http_build_query(array_merge($pagination->uri, array('page' => $pagination->current - 1)));
            } else {
                echo '#';
            }
                ?>">&larr; Older</a>
            </li>
            <li>
                Page <?php echo $pagination->current?> of <?php echo number_format($pagination->totalPage) ?>, found <?php echo number_format($pagination->totalResult) ?> jobs
            </li>
            <li class="next<?php
            if ($pagination->current == $pagination->totalPage) {
                echo ' disabled';
            }?>">
                <a href="<?php
            if ($pagination->current < $pagination->totalPage) {
                echo $pagination->baseUrl . http_build_query(array_merge($pagination->uri, array('page' => $pagination->current + 1)));
            } else {
                echo '#';
            }
                ?>">Newer &rarr;</a>
            </li>
            </ul>
        <?php
        }
    }

    public static function renderJobStats($stats)
    {
        ?>
        <ul class="stats unstyled clearfix split-four" ng-controller="JobsCtrl" ng-cloak>
            <li id="global-worker-stats">
                <a href="/jobs/view">
                    <strong ng-init="stats.processed='<?php echo $stats[ResqueStat::JOB_STATUS_COMPLETE] ?>'">{{stats.processed|number}}</strong>
                    <b>Processed</b> jobs
                </a>
            </li>
            <li><div>
                <strong class="warning" ng-init="stats.failed='<?php echo $stats[ResqueStat::JOB_STATUS_FAILED]?>'">{{stats.failed|number}}</strong>
                <b>Failed</b> jobs</div>
            </li>
            <li>
                <a href="/jobs/pending">
                    <strong ng-init="stats.pending='<?php echo $stats[ResqueStat::JOB_STATUS_SCHEDULED]?>'">{{stats.pending|number}}</strong>
                    <b>Pending</b> jobs
                </a>
            </li>
            <li>
                <a href="/jobs/scheduled">
                    <strong ng-init="stats.scheduled='0'">{{stats.scheduled|number}}</strong>
                    <b>Scheduled</b> jobs
                </a>
            </li>
        </ul>

        <?php
    }

    public static function renderQueuesStatsTable()
    {
        echo '<div ng-controller="QueuesCtrl" ng-cloak>';
        echo '<h2>Queues <span class="badge badge-info">{{length}}</span></h2>';
        echo '<table class="table table-condensed table-greyed"><thead>'.
            '<tr><th class="name">Name</th><th>Pending jobs</th><th>Total jobs</th><th>Workers</th></tr></thead><tbody>';

        echo '<tr ng-repeat="queue in queues">
            <td class="name">{{queue.name}}</td>
            <td><a href="/jobs/pending?queue={{queue.name|urlencode}}">{{queue.stats.pendingjobs|number}}</a></td>
            <td><a href="/jobs/view?queue={{queue.name|urlencode}}"><div style="position:relative;"><span class="chart-bar" style="width:{{queue.stats.totaljobsperc}}%"></span><b>{{queue.stats.totaljobs|number}}</b></a></div></td>
            <td>{{queue.stats.workerscount}}</td>
        </tr>';


        echo '</tbody></table></div>';
    }
}
