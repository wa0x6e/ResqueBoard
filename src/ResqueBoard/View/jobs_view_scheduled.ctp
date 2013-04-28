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
 * @author        Wan Qi Chen <kami@kamisama.me>
 * @copyright     Copyright 2012, Wan Qi Chen <kami@kamisama.me>
 * @link          http://resqueboard.kamisama.me
 * @package       resqueboard
 * @subpackage	  resqueboard.template
 * @since         1.5.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>


	<ul class="nav nav-tabs page-nav-tab">
	    <li>
	    	<a href="/jobs/view">Processed Jobs</a>
	    </li>
	    <li>
	    	<a href="/jobs/pending">Pending Jobs</a>
	    </li>
	    <li class="active">
	    	<a href="/jobs/scheduled" title="View all scheduled jobs">Scheduled Jobs</a>
	    </li>
    </ul>

	<div class="with-sidebar" ng-controller="ScheduledJobsCtrl" ng-cloak>

		<div class="ftr-bloc">
			<h3>Future scheduled jobs activities</h3>
			<div id="scheduled-jobs-graph"></div>
		</div>

		<div class="bloc">
			<div class="knight-unit smaller" ng-show="jobs.length==0 && date==false"><i class="icon-briefcase icon"></i><p class="tagline">Click on the graph to show the associated jobs</p></div>

			<button ng-hide="jobs.length==0 && date==false" ng-click="clear()" type="button" class="close">×</button>
	        <h3 ng-hide="jobs.length==0 && date==false"><ng-pluralize count="jobs.length" when="{'0': 'No job', '1': '1 job', 'other': '{} jobs'}"></ng-pluralize> for <mark class="light">{{date}}</mark></h3>

	        <div ng-show="loading" class="alert alert-info">Loading datas ...</div>

	        <div ng-show="date!=false && jobs.length==0" class="alert">No job found for this period</div>

			<ng-include src="'/partials/jobs-list.html'"></ng-include>
		</div>
	</div>

</div>

	<div class="sidebar">

		<div class="page-header">
			<h3>Quick Stats <i class="icon-bar-chart"></i></h3>
		</div>

		<ul class="stats unstyled clearfix">
			<li><div>
				<strong><?php echo number_format($totalScheduledJobs); ?></strong>
				Total <b>scheduled</b> jobs</div>
			</li>
			<li><div>
				<strong><?php echo number_format($futureScheduledJobs); ?></strong>
				Jobs <b>waiting</b> to be queued</div>
			</li>
			<li><div>
				<strong><?php echo number_format($pastScheduledJobs); ?></strong>
				<b>Past</b> jobs</div>
			</li>
		</ul>
	</div>