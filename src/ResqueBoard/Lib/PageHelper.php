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
 * PageHelper Class
 *
 * Various global helpers functions
 *
 * @subpackage ResqueBoard.Lib
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @since      2.0.0
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
        <ul class="stats unstyled clearfix split-four" ng-controller="jobController" ng-cloak>
            <li id="global-worker-stats">
                <a href="jobs/view">
                    <strong ng-init="stats.processed='<?php echo $stats[ResqueStat::JOB_STATUS_COMPLETE] ?>'">{{stats.processed|number}}</strong>
                    <b>Processed</b> jobs
                </a>
            </li>
            <li><div>
                <strong class="warning" ng-init="stats.failed='<?php echo $stats[ResqueStat::JOB_STATUS_FAILED]?>'">{{stats.failed|number}}</strong>
                <b>Failed</b> jobs</div>
            </li>
            <li>
                <a href="jobs/scheduled">
                    <strong>{{stats.scheduled|number}}</strong>
                    <b>Scheduled</b> jobs
                </a>
            </li>
            <li>
                <a href="jobs/pending">
                    <strong ng-init="stats.pending='0'">{{stats.pending|number}}</strong>
                    <b>Pending</b> jobs
                </a>
            </li>
        </ul>

        <?php
    }
}
