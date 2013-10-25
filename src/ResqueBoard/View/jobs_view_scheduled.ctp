<?php
/**
 * Scheduled Jobs template
 *
 * Website scheudled jobs page
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package    ResqueBoard
 * @subpackage ResqueBoard.View
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @copyright  2012-2013 Wan Qi Chen
 * @link       http://resqueboard.kamisama.me
 * @since      1.5.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>


	<ul class="nav nav-tabs page-nav-tab">
	    <li>
	    	<a href="jobs/view">Processed Jobs</a>
	    </li>
	    <li>
	    	<a href="jobs/pending">Pending Jobs</a>
	    </li>
	    <li class="active">
	    	<a href="jobs/scheduled" title="View all scheduled jobs">Scheduled Jobs</a>
	    </li>
    </ul>

	<div class="with-sidebar">

		<div class="ftr-bloc">
			<h3>Future scheduled jobs activities</h3>
			<div id="scheduled-jobs-graph">
				<i class="fa fa-chevron-left graph-browse-previous" rel="prev" data-event="tooltip" title="Previous"></i>
        		<i class="fa fa-chevron-right graph-browse-next" rel="next" data-event="tooltip" title="Next"></i>
			</div>
		</div>

		<div class="bloc" ng-cloak>
			<div class="knight-unit smaller" ng-show="jobs.length==0 && date==false"><i class="icon-briefcase icon"></i><p class="tagline">Click on the graph to show the associated jobs</p></div>

			<button ng-hide="jobs.length==0 && date==false" ng-click="clear()" type="button" class="close">×</button>
	        <h3 ng-hide="jobs.length==0 && date==false"><ng-pluralize count="jobs.length" when="{'0': 'No job', '1': '1 job', 'other': '{} jobs'}"></ng-pluralize> for <mark class="light">{{date}}</mark></h3>

	        <div ng-show="loading" class="alert alert-info">Loading datas ...</div>

	        <div ng-show="date!=false && jobs.length==0" class="alert">No job found for this period</div>

			<ng-include src="'partials/jobs-list.html'"></ng-include>
		</div>
	</div>

</div>

	<div class="sidebar">

		<div class="page-header">
			<h3>Quick Stats <i class="icon-bar-chart"></i></h3>
		</div>

		<ul class="stats unstyled clearfix" ng-cloak>
			<li><div>
				<strong>{{stats.total|number}}</strong>
				Total <b>scheduled</b> jobs</div>
			</li>
			<li><div>
				<strong>{{stats.future|number}}</strong>
				Jobs <b>waiting</b> to be queued</div>
			</li>
			<li><div><i class="icon-exclamation-sign discret-icon pull-right" popover-placement="left" popover-title="Expired job help"
				popover="Expired scheduled jobs are jobs that are still waiting in the scheduled queue, instead
				of being moved to a regular queue to be executed. Check that the scheduler worker is started." popover-trigger="mouseenter"></i>
				<strong>{{stats.past|number}}</strong>
				<b>Expired</b> jobs</div>
			</li>
		</ul>
	</div>