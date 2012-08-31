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
		<h1>Jobs <small class="subtitle">View jobs details</small></h1>
	</div>
	<div class="row">

		<div class="span12">
		<h2>Jobs distribution</h2>

		<div class="span6">
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
					if (count($pieDatas <= count($jobsRepartitionStats->stats))) {
						$pieDatas[] = array('name' => 'Other', 'count' => $total);
					}

					echo "<script type='text/javascript'>";
					echo "$(document).ready(function() { ";
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
						echo '<b>' . $stat['percentage'] . '%</b></div></div></td>';
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
						echo '<b>' . $p . '%</b></div></div></td>';
						echo '</tr>';
					}

					echo '<tr class="info">';
					echo '<td>Total</td>';
					echo '<td class="stats-nb">' . number_format($jobsRepartitionStats->total) . '</td>';
					echo '<td class="stats-nb">100%</td>';
					echo '</tr>';

					?>
				</tbody>
			</table>



		</div>
		<div class="row">

		<div class="span8">

		<?php

		if ($searchToken !== null) {
			echo '<h2>Results for <mark>' . $searchToken . '</mark></h2>';
		} else {
			echo '<h2>'.$pageTitle. '</h2>';
		}

		if (!empty($jobs)) {

			?>
			<div class="breadcrumb clearfix">
				<div class="pull-right">
				<?php if (isset($pagination)) { ?>
					<form class="form-inline" data-event="ajax-pagination">
						<label>Display
						<select class="span1">
							<?php
							foreach ($resultLimits as $limit) {
								echo '<option value="'.$pagination->baseUrl . $limit . '/' . $pagination->current.'"';
								if ($limit == $pagination->limit) {
									echo ' selected="selected"';
								}
								echo '>'.$limit.'</option>';
							}?>
						</select>
						</label>
					</form>
					<?php } ?>
					<div class="btn-group">
						<button class="btn" data-event="expand-all tooltip" title="Expand all"><i class="icon-folder-open"></i></button>
						<button class="btn" data-event="collapse-all tooltip" title="Collapse all"><i class="icon-folder-close"></i></button>
					</div>
				</div>
			<?php if (isset($pagination)) {
				echo 'Page ' . $pagination->current .' of ' . $pagination->totalPage . ', found ' . $pagination->totalResult . ' jobs';
				} ?>
			</div>
			<p>All time are UTC <?php echo date('P', strtotime($jobs[0]['time'])); ?>, current server time is <?php echo date('r'); ?></p>
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
							<p><i class="icon-time<?php if ($job['status'] == ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED) echo ' icon-white' ?>"></i> <b>Added on </b><?php echo $job['time']; ?></p>

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

			if (isset($pagination)) {
				?>
						<ul class="pager">
						<li class="previous<?php if ($pagination->current == 1) echo ' disabled'?>">
							<a href="<?php
								if ($pagination->current > 1) {
									echo $pagination->baseUrl . $pagination->limit . '/' . ($pagination->current - 1);
								} else {
									echo '#';
								}
							?>">&larr; Older</a>
						</li>
						<li>
							Page <?php echo $pagination->current?> of <?php echo $pagination->totalPage ?>, found <?php echo $pagination->totalResult?> jobs
						</li>
						<li class="next<?php if ($pagination->current == $pagination->totalPage) {
							echo ' disabled';
						}?>">
							<a href="<?php
								if ($pagination->current < $pagination->totalPage) {
									echo $pagination->baseUrl . $pagination->limit . '/' . ($pagination->current + 1);
								} else {
									echo '#';
								}
							?>">Newer &rarr;</a>
						</li>
						</ul>

				<?php
			}


		}
		elseif ($searchToken !== null) {
			?>
				<div class="alert alert-info">
					No jobs found matching <mark><?php echo $searchToken?></mark>
				</div>
			<?php
		} else {
			?>
				<div class="alert alert-info">
					Nothing to display
				</div>
			<?php
		}
		?>
		</div>

		<div class="span4">



		<div class="well" style="padding: 8px 0;">
		<ul class="nav nav-list">
			<li class="nav-header">
				Search Job
			</li>
			<li>
			<form class="form-search" action="/jobs" method="POST">
				<input type="text" name="job_id" class="input-medium search-query" placeholder="Job #Id"/>
				<button type="submit" class="btn">Search Job</button>
			</form>
			</li>
			<li class="nav-header">
				Active workers <span class="label"><?php echo count($workers); ?></span>
			</li>
			<?php
				foreach ($workers as $worker) {
					echo '<li><a href="/jobs/'.$worker['host'] . '/' . $worker['process'].'"><i class="icon-cog"></i>';
					echo $worker['host'] . ':' . $worker['process'];
					echo '</a></li>';
				}
			?>
		</ul>
		</div>
		</div>
	</div>

</div>