<?php
/**
 * Index template
 *
 * Website home page
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
 * @since         1.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>

<script id="jobs-tpl" type="text/x-jsrender">
			 <li class="accordion-group">
				<div class="accordion-heading" data-toggle="collapse" data-target="#{{>job_id}}">
					<div class="accordion-toggle">

						<span class="label label-info pull-right">{{>worker}}</span>
						<h4>#{{>job_id}}</h4>
						<small>Waiting for <code>{{>class}}</code> in <span class="label label-success">{{>queue}}</span></small>
					</div>
				</div>
				<div class="collapse accordion-body" id="{{>job_id}}">
			   <div class="accordion-inner">
			   <h5>Job arguments</h5>
				<pre class=""><code>{{>args}}</code></pre></div></div>
			</li>
</script>

<script type="text/javascript">
	$(document).ready(function() {
		listenToWorkersJob("pie", "list");
		listenToJobsActivities();
		QueuesList.init();
	});
</script>


<ul class="stats unstyled clearfix split-four">
	<li id="global-worker-stats">
		<a href="/jobs/view">
			<strong data-status="processed"><?php echo number_format($stats[ResqueBoard\Lib\ResqueStat::JOB_STATUS_COMPLETE]) ?></strong>
			<b>Processed</b> jobs
		</a>
	</li>
	<li><div>
		<strong class="warning" data-status="failed"><?php echo number_format($stats[ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED])?></strong>
		<b>Failed</b> jobs</div>
	</li>
	<li>
		<a href="/jobs/pending">
			<strong>00x00</strong>
			<b>Pending</b> jobs
		</a>
	</li>
	<li>
		<a href="/jobs/scheduled">
			<strong><?php echo number_format($stats[ResqueBoard\Lib\ResqueStat::JOB_STATUS_SCHEDULED])?></strong>
			<b>Scheduled</b> jobs
		</a>
	</li>
</ul>


<div class="ftr-bloc">
	<h3>Last activities</h3>
	<div id="lastest-jobs"></div>
</div>



<div class="row">
	<div id="job-details" class="modal hide">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">×</button>
			<h3>Jobs <span class="badge badge-info"></span></h3>
		</div>
		<ul class="modal-body unstyled job-details">
		</ul>
	</div>
</div>


<div class="span5">

	<h2>Workers <span class="badge badge-info workers-count"><?php echo count($workers)?></span></h2>


	<div id="worker-form" class="modal hide"></div>
	<div id="worker-details" class="modal hide">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">×</button>
			<h3>Worker properties</h3>
		</div>
		<ul class="modal-body unstyled">
		</ul>
	</div>

    <div class="workers-list">
		<?php  ResqueBoard\Lib\WorkerHelper::renderList($stats, $workers, $readOnly); ?>
	</div>

	</div>

	<div class="span6">

		<h2>Queues <span class="badge badge-info queues-count"><?php echo count($queues)?></span></h2>

		<?php
		    echo '<table class="table table-condensed table-greyed"><thead>'.
			    '<tr><th class="name">Name</th><th>Pending jobs</th><th>Total jobs</th><th>Workers</th></tr></thead><tbody>';

			if (!empty($queues)) {
				foreach ($queues as $queueName => $queueStat) {
					if ($queueName === ResqueScheduler\ResqueScheduler::QUEUE_NAME) {
						continue;
					} ?>
				<tr>
					<td class="name"><?php echo $queueName?></td>
					<td class=""><a href="/jobs/pending?queue=<?php echo $queueName ?>"><?php echo number_format($queueStat['jobs']); ?></a></td>
					<td class=""><a href="/jobs/view?queue=<?php echo $queueName ?>">00x00</a></td>
					<td>00x00</td>

				</tr>
			<?php
			    }
			}
			echo '</tbody></table>';
		 ?>




		<h2>Jobs activities</h2>
		<div id="latest-jobs-graph"></div>
		<div id="latest-jobs-list">
			<p>Click on the graph to show the associated jobs</p>
		</div>
		<script id="latest-jobs-list-tpl" type="text/x-jsrender">
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
						<p><i class="icon-time"></i> <b>Added on </b>{{>created}}</p>
						<pre class="job-args"><code class="language-php">{{>args}}</code></pre>
					</div>
				</div>
			</li>
		</script>

</div>