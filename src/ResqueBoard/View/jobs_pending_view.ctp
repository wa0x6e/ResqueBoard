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
 * @since         1.5.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
	<div class="page-header">
		<h1>Jobs Browser</h1>
	</div>

    <ul class="nav nav-tabs page-nav-tab">
	    <li>
	    	<a href="/jobs/view">Processed Jobs</a>
	    </li>
	    <li class="active">
	    	<a href="/jobs/pending">Pending Jobs</a>
	    </li>
	    <li>
	    	<a href="/jobs/scheduled" title="View all scheduled jobs">Scheduled Jobs</a>
	    </li>
    </ul>

	<div class="with-sidebar">
		<div class="bloc">

		<?php

		echo '<h2>Pending Jobs';

		if (isset($pagination->uri['queue'])) {
			echo ' from <mark>' .  $pagination->uri['queue'] . '</mark>';
		}

		if (!empty($pagination->totalResult)) {
			echo '<span class="badge pull-right">' . number_format($pagination->totalResult) . ' results</span>';
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

			<?php

			\ResqueBoard\Lib\JobHelper::renderJobs($jobs, 'No pending jobs');

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

				<div class="alert">No pending jobs found</div>

			<?php
		}
		?>
		</div>
	</div>
</div>

	<div class="sidebar">

			<h3>Stats</h3>
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
						<td><?php echo $queueName; ?> <?php if (empty($stats['workers'])) {
							echo '<i class="icon-warning-sign" title="This queue is not polled by any worker" data-event="tooltip"></i>';
						} ?></td>
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
