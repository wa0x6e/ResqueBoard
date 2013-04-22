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

	$message = '';
	if (!empty($pastScheduledJobs)) :
		$message = '<div class="alert alert-error alert-page">There is some scheduled jobs past their due date. Is the Scheduler Worker running ?</div>';
	endif; ?>

	<div class="page-header">
		<h1>Jobs Browser</h1>
	</div>
	<?php //echo $message ?>

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

	<div class="with-sidebar">

		<div class="ftr-bloc">
			<h3>Future scheduled jobs activities</h3>
			<div id="scheduled-jobs-graph"></div>
		</div>

		<script id="scheduled-jobs-list-tpl" type="text/x-jsrender">
				<li class="accordion-group">
				<div class="accordion-heading" data-toggle="collapse" data-target="#{{>id}}">
					<div class="accordion-toggle">
						<span class="job-status-icon" data-event="tooltip" data-original-title="Job scheduled">
						<img src="/img/job_scheduled.png" title="Job scheduled" height="24" width="24"></span>

						<h4>#{{>id}}</h4>

						<small>Performing <code>{{>class}}</code> in
						<span class="label label-success">{{>queue}}</span></small>

					</div>
				</div>
				<div class="collapse accordion-body" id="{{>id}}">
					<div class="accordion-inner">
						<p>
							<i class="icon-time"></i> <b>Added on </b>{{>created}}</p>


						<pre class="job-args"><code class="language-php">{{>args}}</code></pre>
					</div>
				</div>
			</li>
		</script>

		<div class="bloc">
			<div id="scheduled-jobs-list"></div>
		</div>
	</div>

</div>

	<div class="sidebar">
		<ul class="stats unstyled clearfix">
			<li><div>
				<strong><?php echo number_format($stats['total']['scheduled']); ?></strong>
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