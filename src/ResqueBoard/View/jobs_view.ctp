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
		<div class="span8">

		<?php

		if (!empty($searchToken)) {
			echo '<h2>Search results</h2>';
		} else {
			echo '<h2>Latest Jobs</h2>';
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
				<li class="accordion-group<?php if ($job['status'] == ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED) echo ' error' ?>">
					<div class="accordion-heading" data-toggle="collapse" data-target="#<?php echo $job['job_id']?>">
						<div class="accordion-toggle">
							<span title="Job <?php echo $jobStatus[$job['status']] ?>" class="job-status-icon" data-event="tooltip">
							<img src="/img/job_<?php echo $jobStatus[$job['status']] ?>.png" title="Job <?php echo $jobStatus[$job['status']] ?>" height=24 width=24 /></span>
							<span class="label label-info pull-right"><?php echo $job['worker']?></span>
							<h4>#<?php echo $job['job_id']?></h4>
							<time datetime="<?php echo date('c', strtotime($job['time']))?>" title="<?php echo date('c', strtotime($job['time']))?>">
								<i class="icon-time"></i> <?php echo date('H:i:s', strtotime($job['time'])); ?>
							</time>
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


		}
		elseif (!empty($errors)) {
		?>
			<div class="alert alert-error">
				Errors in your search request
			</div>
		<?php
		} elseif ($searchToken !== null) {
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

			<h2>Search</h2>
			<form class="" action="/jobs/view" method="GET">
				<div class="input-append">
					<input type="text" name="job_id" class="span3" placeholder="Job #Id"/>
					<button type="submit" class="btn"><i class="icon-search"></i></button>
				</div>
			</form>

			<h2>Advanced search</h2>
			<form action="/jobs/view" method="GET">
				<fieldset>
					<div class="control-group">
						<label for="job-search-class">Job class</label>
						<input type="text" name="class" id="job-search-class" placeholder="ClassName" value="<?php echo $searchData['class'] ?>" />
						<span class="help-inline">Multiple classes separated with a comma</span>
					</div>
					<div class="control-group">
						<label for="job-search-queue">Queue</label>
						<input type="text" name="queue" id="job-search-queue" placeholder="Queue name" value="<?php echo $searchData['queue'] ?>" />
						<span class="help-inline">Multiple queues separated with a comma</span>
					</div>
					<div class="control-group<?php if (!empty($errors['date_after'])) echo ' error' ?>">
						<label>After</label>
						<input type="text" name="date_after" placeholder="YYYY-MM-DD hh:mm:ss" value="<?php echo $searchData['date_after'] ?>" />
						<?php if (!empty($errors['date_after'])) { echo getFormErrors($errors['date_after']); } ?>
					</div>
					<div class="control-group<?php if (!empty($errors['date_before'])) echo ' error' ?>">
						<label>Before</label>
						<input type="text" name="date_before" placeholder="YYYY-MM-DD hh:mm:ss" value="<?php echo $searchData['date_before'] ?>" />
						<?php if (!empty($errors['date_before'])) { echo getFormErrors($errors['date_before']); } ?>
					</div>
				</fieldset>

				<fieldset>
					<legend>Workers</legend>
					<?php
					foreach ($workers as $worker) {
						echo '<label class="checkbox">'.$worker['host'] . ':' . $worker['process'];
						echo '<input type="checkbox" name="worker[]" value="'.$worker['host'] . ':' . $worker['process'].'"'. ((in_array($worker['host'] . ':' . $worker['process'], $searchData['worker'])) ? ' checked="checked"' : '') .' />';
						echo '</label>';
					}?>
					<label class="checkbox">Old workers
						<input type="checkbox" name="worker[]" value="old"<?php if (in_array('old', $searchData['worker'])) echo ' checked="checked"'; ?> />
					</label>

					<label>Particular workers</label>
					<input type="text" name="workers" placeholder="Worker name" value="<?php echo $searchData['workers'] ?>"/>

					<span class="help-inline">Multiple queues separated with a comma</span>

				</fieldset>

				<hr>

				<button class="btn btn-primary" type="submit">Search</button>

			</form>
		</div>
	</div>

</div>

<?php

	function getFormErrors($message)
	{
		return '<span class="help-inline">' . $message . '</span>';
	}

?>