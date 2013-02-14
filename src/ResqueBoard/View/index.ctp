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
<div class="container" id="main">

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
							<pre class="">{{>args}}</pre></div></div>
						</li>
			</script>

			<script type="text/javascript">
				$(document).ready(function() {
					listenToWorkersJob("pie", "list");
					listenToJobsActivities();
					QueuesList.init();
				});
			</script>

			<div class="row">
				<div class="span12">
					<div class="page-header">
						<h2>Jobs</h2>
					</div>

					<div class="row">
						<div class="span8">
							<h3>Lastest 4 minutes activities</h3>
							<div id="lastest-jobs"></div>
						</div>

						<div id="job-details" class="modal hide">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">×</button>
								<h3>Jobs <span class="badge badge-info"></span></h3>
							</div>
							<ul class="modal-body unstyled">
							</ul>
						</div>

						<div class="span4">

							<div class="workers-list-item">

								<div class="worker-list-inner">
								<h3 class="sub">Total job stats</h3>
									<div class="worker-stats clearfix" id="global-worker-stats">
									<div class="chart-pie" data-type="chart" data-chart-type="pie" data-processed="<?php
									echo $stats['total']['processed'] - $stats['total']['failed'] ?>"
										data-failed="<?php echo $stats['total']['failed']?>"></div>
										<div class="stat-count">
											<b data-status="processed"><?php echo number_format($stats['total']['processed']) ?></b>
											Processed
										</div>
										<div class="stat-count">
											<b class="warning" data-status="failed"><?php echo number_format($stats['total']['failed'])?></b>
											Failed
										</div>
								</div>

								<h3 class="sub">Active workers job stats</h3>

									<div class="worker-stats clearfix" id="active-worker-stats">
									<div class="chart-pie" data-type="chart" data-chart-type="pie" data-processed="<?php
									echo $stats['active']['processed'] - $stats['active']['failed'] ?>"
										data-failed="<?php echo $stats['active']['failed']?>"></div>
										<div class="stat-count">
											<b data-status="processed"><?php echo number_format($stats['active']['processed'])?></b>
											Processed
										</div>
										<div class="stat-count">
											<b class="warning" data-status="failed"><?php echo number_format($stats['active']['failed'])?></b>
											Failed
										</div>

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="span8">
				<div class="page-header">
				<h2>Workers <span class="badge badge-info workers-count"><?php echo count($workers)?></span></h2>
				</div>

				<div id="worker-form" class="modal hide"></div>
				<div id="worker-details" class="modal hide">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">×</button>
						<h3>Worker properties</h3>
					</div>
					<ul class="modal-body unstyled">
					</ul>
				</div>

                <div class="row workers-list">
				<?php  ResqueBoard\Lib\WorkerHelper::renderList($stats, $workers, $readOnly); ?>
				</div>

				</div>

				<div class="span4 queues-list">
					<div class="page-header">
					<h2>Queues <span class="badge badge-info queues-count"><?php echo count($queues)?></span></h2>
					</div>
					<?php
					    echo '<table class="table table-condensed"><thead>'.
						    '<tr><th>Name</th><th>Worker count</th></tr></thead><tbody>';

						if (!empty($queues)) {
							foreach ($queues as $queueName => $queueStat) {
								if ($queueName === ResqueScheduler\ResqueScheduler::QUEUE_NAME) {
									continue;
								} ?>
							<tr>
								<td class="queues-list-name"><?php echo $queueName?></td>
								<td class="queues-list-count"><?php echo $queueStat['jobs']?></td>
							</tr>
						<?php
						    }
						}
						echo '</tbody></table>';
					 ?>


					<div class="page-header">
						<h2>Scheduler Worker</h2>
						</div>

						<div id="worker-form" class="modal hide"></div>
						<div id="worker-details" class="modal hide">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">×</button>
								<h3>Worker properties</h3>
							</div>
							<ul class="modal-body unstyled">
							</ul>
						</div>

		                <div class="row workers-list">
						<?php ResqueBoard\Lib\SchedulerWorkerHelper::renderList($stats, $schedulerWorkers, $readOnly); ?>
						</div>

					</div>

				</div>

			</div>
		</div>