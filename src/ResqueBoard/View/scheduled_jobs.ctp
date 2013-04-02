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
<div class="container" id="main">
	<div class="page-header">
		<h1>Scheduled Jobs</h1>
	</div>

	<div class="row">

		<div class="span12">

		<?php if (!empty($pastScheduledJobs)) : ?>
		<div class="alert alert-error">There is some scheduled jobs past their due date. Is the Scheduler Worker running ?</div>
		<?php endif; ?>




			<ul class="jobs-stats unstyled clearfix">
				<li class="secondary clearfix"><div>
					<strong><?php echo number_format($stats['total']['scheduled']); ?></strong>
					total scheduled jobs so far</div>
				</li>
				<li class="secondary clearfix"><div>
					<strong><?php echo number_format($futureScheduledJobs); ?></strong>
					Jobs waiting to be queued</div>
				</li>
				<li class="secondary clearfix"><div>
					<strong><?php echo number_format($pastScheduledJobs); ?></strong>
					past jobs</div>
				</li>
			</ul>

			<h2>Future scheduled jobs activites</h2>

			<div id="scheduled-jobs-graph"></div>

			<hr/>

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
			<div id="scheduled-jobs-list">

			</div>

		</div>

	</div>


</div>