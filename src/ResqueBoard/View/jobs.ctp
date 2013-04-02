<?php
/**
 * job template
 *
 * Website jobs page
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

	$jobStatus = array(
				ResqueBoard\Lib\ResqueStat::JOB_STATUS_WAITING => 'waiting',
				ResqueBoard\Lib\ResqueStat::JOB_STATUS_RUNNING => 'running',
				ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED => 'failed',
				ResqueBoard\Lib\ResqueStat::JOB_STATUS_COMPLETE => 'complete'
			);


?><div class="container" id="main">
	<div class="page-header">
		<h1>Jobs <small class="subtitle">Jobs dashboard</small></h1>
	</div>
	<div class="row">

		<div class="span6">
			<h2><a href="/jobs/distribution/class" title="View jobs distribution by classes">Distribution by classes</a></h2>

			<div id="jobRepartition">
				<?php
					$pieDatas = array();
					$total = 100;
					foreach($jobsRepartitionStats->stats as $stat) {
						if ($stat['percentage'] >= 15) {
							$pieDatas[] = array('name' => $stat['_id'], 'count' => $stat['percentage']);
							$total -= $stat['percentage'];
						}
					}
					if (count($pieDatas) < count($jobsRepartitionStats->stats)) {
						$pieDatas[] = array('name' => 'Other', 'count' => $total);
					}

					$diff = $jobsStats->oldest === null ? 0 : date_diff($jobsStats->oldest, new DateTime())->format('%a');
					$jobsDailyAverage = empty($diff) ? 0 : round($jobsStats->total / $diff);

					echo "<script type='text/javascript'>";
					echo "$(document).ready(function() { ";
						echo "jobsLoad();";
						echo "monthlyJobsLoad(". $jobsDailyAverage .");";
						echo "pieChart('jobRepartition', " . $jobsRepartitionStats->total . ", " . json_encode($pieDatas) . ");";
					echo "})</script>";
				?>
			</div>

			<table class="table table-condensed table-hover">
				<thead>
					<tr>
						<th>Job class</th>
						<th class="stats-nb">Count</th>
						<th class="stats-nb">Distribution</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$total = 0;
					foreach ($jobsRepartitionStats->stats as $stat) {
						echo '<tr>';
						echo '<td>' . $stat['_id'] . '</td>';
						echo '<td class="stats-nb">' . number_format($stat['value']) . '</td>';
						echo '<td class="stats-nb"><div style="position:relative;">';
						echo '<span class="chart-bar" style="width:' . $stat['percentage'] . '%;"></span>';
						echo '<b>' . ($stat['percentage'] != 0 ? '' : '~') . $stat['percentage'] . '%</b></div></div></td>';
						echo '</tr>';

						$total += $stat['value'];
					}

					if ($total < $jobsRepartitionStats->total) {
						$p = round(($jobsRepartitionStats->total - $total) / $jobsRepartitionStats->total * 100, 2);

						echo '<tr>';
						echo '<td>Other</td>';
						echo '<td class="stats-nb">' . number_format($jobsRepartitionStats->total - $total) . '</td>';
						echo '<td class="stats-nb"><div style="position:relative;">';
						echo '<span class="chart-bar" style="width:' . $p . '%;"></span>';
						echo '<b>' . ($p != 0 ? '' : '~') . $p . '%</b></div></div></td>';
						echo '</tr>';
					}

					if ($jobsRepartitionStats->total > 0) {
						echo '<tr class="info">';
						echo '<td>Total</td>';
						echo '<td class="stats-nb">' . number_format($jobsRepartitionStats->total) . '</td>';
						echo '<td class="stats-nb">100%</td>';
						echo '</tr>';
					} else {
						echo '<tr class="info">';
						echo '<td colspan=3>No jobs found</td>';
						echo '</tr>';
					}

					?>
				</tbody>
			</table>
			<a href="jobs/distribution">View all</a>
		</div>

		<div class="span6">

			<ul class="jobs-stats unstyled clearfix">
				<li class="total clearfix"><div>

				<div class="pull-right secondary">
				<strong><?php echo number_format($jobsStats->total_active) ?></strong>
				from active workers
				</div>

				<strong><?php echo number_format($jobsStats->total) ?></strong> <span class="pull-left">Total Jobs</span>
				<span class="chart-bar"><span class="chart-bar-in" style="width:<?php echo $jobsStats->perc[ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED] ?>%"></span></span>
				<small><?php echo $jobsStats->perc[ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED] ?> % fails</small>
				</div></li>
				<li><div><strong><?php echo number_format($jobsStats->count[ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED]) ?></strong> failed Jobs</div></li>
				<li><div><strong><?php echo number_format($jobsStats->count[ResqueBoard\Lib\ResqueStat::JOB_STATUS_RUNNING]) ?></strong> Running jobs</div></li>
				<li>
					<div><a href="/jobs/pending" title="View all pending jobs"><strong><?php echo number_format($jobsStats->count[ResqueBoard\Lib\ResqueStat::JOB_STATUS_WAITING]) ?></strong> Waiting jobs</a></div>

				</li>
			</ul>

			<h2>Jobs load <small>for the past hour</small></h2>
			<div id="jobs-load" style="position:relative;"></div>


			<h2><form class="pull-right">

					<?php

					if ($jobsStats->oldest === null) {
						$jobsStats->oldest = new DateTime();
					}

					if ($jobsStats->newest === null) {
						$jobsStats->newest = new DateTime();
					}

					$startDate = $jobsStats->oldest->setDate($jobsStats->oldest->format('Y'), $jobsStats->oldest->format('m'), 1);
					$endDate = $jobsStats->newest->setDate($jobsStats->newest->format('Y'), $jobsStats->newest->format('m'), 1);

					$dateRange = array(clone $startDate);
					while ($startDate->modify('first day of next month') < $endDate) {
						$dateRange[] = clone $startDate;
					}


					echo '<select class="span2" id="jobs-load-monthly-selector">';
					$i = 0;
					foreach ($dateRange as $date) {
						echo '<option value="'.$date->format('Y-m').'"'. (++$i == count($dateRange) ? ' selected="selected"' : '') .'>'. $date->format('F Y') .'</option>';
					}
					echo '</select>';

					?>

			</form>
			Jobs load <small>by month</small></h2>

			<div id="jobs-load-monthly"></div>
		</div>

	</div>

	<div class="row">
		<div class="span12">
		<hr>
		</div>


		<div class="span7">

		<?php


		echo '<h2><a href="/jobs/view">Latest Jobs</a></h2>';


		if (!empty($jobs)) {

			?>
			<div class="breadcrumb clearfix">
				<div class="pull-right">
					<div class="btn-group">
						<button class="btn" data-event="expand-all tooltip" title="Expand all"><i class="icon-folder-open"></i></button>
						<button class="btn" data-event="collapse-all tooltip" title="Collapse all"><i class="icon-folder-close"></i></button>
					</div>
				</div>
			</div>

			<?php
			echo '<ul class="unstyled" id="job-details">';

			foreach ($jobs as $job) {
				?>
				<li class="accordion-group<?php if ($job['status'] == ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED) echo ' error' ?>">
					<div class="accordion-heading" data-toggle="collapse" data-target="#<?php echo $job['job_id']?>">
						<div class="accordion-toggle">
							<span title="Job <?php echo $jobStatus[$job['status']] ?>" class="job-status-icon" data-event="tooltip">
							<img src="/img/job_<?php echo $jobStatus[$job['status']] ?>.png" title="Job <?php echo $jobStatus[$job['status']] ?>" height=24 width=24 /></span>
							<span class="label label-info pull-right"><?php echo $job['worker']?></span>
							<h4>#<?php echo $job['job_id']?></h4>
							<time datetime="<?php echo date('c', strtotime($job['time']))?>"><i class="icon-time"></i> <?php echo date('H:i:s', strtotime($job['time'])); ?></time>
							<small>Performing <code><?php echo $job['class']?></code> in
							<span class="label label-success"><?php echo $job['queue']?></span></small>

						</div>
					</div>
					<div class="collapse<?php if (count($jobs) == 1) echo ' in'; ?> accordion-body" id="<?php echo $job['job_id']?>">
						<div class="accordion-inner">
							<p>
								<span class="pull-right">Took <?php echo $job['took']; ?> ms</span>
								<i class="icon-time<?php if ($job['status'] == ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED) echo ' icon-white' ?>"></i> <b>Added on </b><?php echo $job['time']; ?></p>

							<?php if (isset($job['log'])) {
								echo '<div class="alert alert-error">' . $job['log'] . '</div>';
							}

							if (isset($job['trace'])) {
								echo '<pre class="job-trace"><code class="language-php">'. $job['trace'] . '</code></pre>';
							}
							?>

							<pre class="job-args"><code class="language-php"><?php echo $job['args'] ?></code></pre>
						</div>
					</div>
				</li>
				<?php
			}
			echo '</ul>';

		} else {
			?>
				<div class="alert alert-info">
					Nothing to display
				</div>
			<?php
		}
		?>
		<a href="/jobs/view" class="btn btn-block btn-small">More jobs</a>
		</div>

		<div class="span5">

			<div class="">
				<h2>Recent failures</h2>
				<?php
			if (!empty($failedJobs)) {


			echo '<ul class="unstyled" id="job-details">';

			foreach ($failedJobs as $job) {
				?>
				<li class="accordion-group<?php if ($job['status'] == ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED) echo ' error' ?>">
					<div class="accordion-heading" data-toggle="collapse" data-target="#<?php echo $job['job_id']?>">
						<div class="accordion-toggle">
							<span title="Job <?php echo $jobStatus[$job['status']] ?>" class="job-status-icon" data-event="tooltip">
							<img src="/img/job_<?php echo $jobStatus[$job['status']] ?>.png" title="Job <?php echo $jobStatus[$job['status']] ?>" height=24 width=24 /></span>
							<span class="label label-info pull-right"><?php echo $job['worker']?></span>
							<h4>#<?php echo $job['job_id']?></h4>
							<time datetime="<?php echo date('c', strtotime($job['time']))?>"><i class="icon-time"></i> <?php echo date('H:i:s', strtotime($job['time'])); ?></time>
							<small>Performing <code><?php echo $job['class']?></code> in
							<span class="label label-success"><?php echo $job['queue']?></span></small>

						</div>
					</div>
					<div class="collapse<?php if (count($jobs) == 1) echo ' in'; ?> accordion-body" id="<?php echo $job['job_id']?>">
						<div class="accordion-inner">
							<p>
								<span class="pull-right">Took <?php echo $job['took']; ?> ms</span>
								<i class="icon-time<?php if ($job['status'] == ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED) echo ' icon-white' ?>"></i> <b>Added on </b><?php echo $job['time']; ?></p>

							<?php if (isset($job['log'])) {
								echo '<div class="alert alert-error">' . $job['log'] . '</div>';
							}

							if (isset($job['trace'])) {
								echo '<pre class="job-trace"><code class="language-php">'. $job['trace'] . '</code></pre>';
							}
							?>

							<pre class="job-args"><code class="language-php"><?php echo $job['args'] ?></code></pre>
						</div>
					</div>
				</li>
				<?php
			}
			echo '</ul>';

		} else {
			?>
				<div class="alert alert-info">
					No failed jobs so far :)
				</div>
			<?php
		}
		?>
			</div>

			<div class="">
				<h2><a href="/jobs/pending" title="View all pending jobs">Latest Pending jobs</a></h2>
				<?php
			if (!empty($pendingJobs)) {


			echo '<ul class="unstyled" id="job-details">';

			foreach ($pendingJobs as $job) {
				?>
				<li class="accordion-group">
					<div class="accordion-heading" data-toggle="collapse" data-target="#<?php echo $job['job_id']?>">
						<div class="accordion-toggle">
							<span title="Job <?php echo $jobStatus[$job['status']] ?>" class="job-status-icon" data-event="tooltip">
							<img src="/img/job_<?php echo $jobStatus[$job['status']] ?>.png" title="Job <?php echo $jobStatus[$job['status']] ?>" height=24 width=24 /></span>
							<h4>#<?php echo $job['job_id']?></h4>
							<small>Performing <code><?php echo $job['class']?></code> in
							<span class="label label-success"><?php echo $job['queue']?></span></small>

						</div>
					</div>
					<div class="collapse<?php if (count($jobs) == 1) echo ' in'; ?> accordion-body" id="<?php echo $job['job_id']?>">
						<div class="accordion-inner">
							<p>
							<?php

							if (isset($job['trace'])) {
								echo '<pre class="job-trace"><code class="language-php">'. $job['trace'] . '</code></pre>';
							}
							?>

							<pre class="job-args"><code class="language-php"><?php echo $job['args'] ?></code></pre>
						</div>
					</div>
				</li>
				<?php
			}
			echo '</ul>';
			echo '<a href="/jobs/pending" title="View more pending jobs" class="btn btn-block btn-small">More pending jobs</a>';

		} else {
			?>
				<div class="alert alert-info">
					No pending jobs
				</div>
			<?php
		}
		?>
			</div>


		</div>
	</div>

</div>