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
		<h1>Jobs</h1>
	</div>

	<div class="row">

		<div class="span12">
		    <ul class="nav nav-tabs">
		    <li>
		    	<a href="/jobs/view">Completed/Failed Jobs</a>
		    </li>
		    <li class="active">
		    	<a href="<?php echo $_SERVER['REQUEST_URI'] ?>">Pending Jobs</a>
		    </li>
		    </ul>
		</div>

		<div class="span7">

		<?php

		echo '<h2>Pending Jobs';

		if (isset($pagination->uri['queue'])) {
			echo ' from <mark>' .  $pagination->uri['queue'] . '</mark>';
		}

		echo'</h2>';

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
								$params = array_merge($pagination->uri, array('limit' => $limit));
								echo '<option value="'.$pagination->baseUrl . http_build_query($params) . '"';
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
				echo 'Page ' . $pagination->current .' of ' . number_format($pagination->totalPage) . ', found ' . number_format($pagination->totalResult) . ' jobs';
				} ?>
			</div>
			<p>Current server time is <?php echo date('r'); ?></p>
			<?php
			echo '<ul class="unstyled infinite-scroll" id="job-details">';

			foreach ($jobs as $job) {
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
									echo $pagination->baseUrl . http_build_query(array_merge($pagination->uri, array('page' => $pagination->current - 1)));
								} else {
									echo '#';
								}
							?>">&larr; Older</a>
						</li>
						<li>
							Page <?php echo $pagination->current?> of <?php echo number_format($pagination->totalPage) ?>, found <?php echo number_format($pagination->totalResult) ?> jobs
						</li>
						<li class="next<?php if ($pagination->current == $pagination->totalPage) {
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


		} else {
			?>
				<div class="alert alert-info">
					Nothing to display
				</div>
			<?php
		}
		?>
		</div>

		<div class="span5">
			<h2>Stats</h2>
			<table class="table-condensed table">
				<tr>
					<th>Queue Name</th>
					<th>Pending Jobs</th>
					<th>Workers Count</th>
				</tr>
				<?php
				$totalJobs = $totalWorkers = 0;
				foreach ($queues as $queueName => $stats) {
					if ($queueName === ResqueScheduler\ResqueScheduler::QUEUE_NAME) continue;
					$totalJobs += $stats['jobs'];
					$totalWorkers += $stats['workers'];
				?>
					<tr<?php if (empty($stats['workers'])) echo ' class="error"'; ?>>
						<td><?php echo $queueName ?></td>
						<td><?php echo $stats['jobs'] > 0 ? ('<a href="/jobs/pending?queue='.$queueName.'" title="View all pending jobs from ' . $queueName . '">' . number_format($stats['jobs']) . '</a>') : 0 ?></td>
						<td><?php echo number_format($stats['workers']) ?></td>
					</tr>
				<?php } ?>
				<tr class="info">
					<td><b>Total</b></td>
					<td><b><?php echo number_format($totalJobs) ?></b></td>
					<td><b><?php echo number_format($totalWorkers) ?></b></td>
				</tr>
			</table>
		</div>
	</div>

</div>
