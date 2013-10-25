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
 * @package    ResqueBoard
 * @subpackage ResqueBoard.View
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @copyright  2012-2013 Wan Qi Chen
 * @link       http://resqueboard.kamisama.me
 * @since      1.0.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>

    <ul class="nav nav-tabs page-nav-tab">
	    <li class="active">
	    	<a href="<?php echo $_SERVER['REQUEST_URI'] ?>" title="View all completed/failed jobs">Processed Jobs</a>
	    </li>
	    <li>
	    	<a href="jobs/pending" title="View all pending jobs">Pending Jobs</a>
	    </li>
	    <li>
	    	<a href="jobs/scheduled" title="View all scheduled jobs">Scheduled Jobs</a>
	    </li>
    </ul>



	<div class="with-sidebar">
	<div class="bloc">


		<?php

		if (!empty($searchToken)) {
			echo '<h2>Search results';
			if (!empty($pagination->totalResult)) {
				echo '<span class="badge pull-right">' . number_format($pagination->totalResult) . ' results</span>';
			}
			echo'</h2>';
		} else {
			echo '<h2>Latest Jobs';
			if (!empty($pagination->totalResult)) {
				echo '<span class="badge pull-right">' . number_format($pagination->totalResult) . ' results</span>';
			}
			echo'</h2>';
		}

		if (!empty($jobs)) {

			?>
			<div class="breadcrumb clearfix">
				<div class="pull-right">
				<?php if (isset($pagination)) { ?>
					<form class="form-inline navigator">
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
						<button class="btn" data-event="expand-all tooltip" title="Expand all"><i class="fa fa-folder-open"></i></button>
						<button class="btn" data-event="collapse-all tooltip" title="Collapse all"><i class="fa fa-folder"></i></button>
					</div>
				</div>
			<?php if (isset($pagination)) {
				echo 'Page ' . $pagination->current .' of ' . number_format($pagination->totalPage) . ', found ' . number_format($pagination->totalResult) . ' jobs';
				} ?>
			</div>

			<?php

			\ResqueBoard\Lib\JobHelper::renderJobs($jobs, 'No jobs found', 'infinite-scroll');
			\ResqueBoard\Lib\PageHelper::renderPagination($pagination);



		}
		elseif (!empty($errors)) {
		?>
			<div class="alert alert-error">
				Errors in your search request
			</div>
		<?php
		} elseif (!empty($searchToken)) {
			?>
				<div class="alert alert-info">
					No jobs found matching <mark class="light"><?php echo $searchToken?></mark>
				</div>
			<?php
		} else {
			?>
				<div class="alert">
					Nothing to display
				</div>
			<?php
		}
		?>
		</div>

</div>
</div>


		<div class="sidebar">
			<div class="bloc">
			<h3>Search</h3>
			<form class="" action="jobs/view" method="GET" role="form">

				<input type="text" name="job_id" class="span2" placeholder="Job #Id"/>
				<button class="btn btn-primary" type="submit">Search</button>

			</form>

			<hr/>

			<h3>Advanced search</h3>
			<form action="jobs/view" method="GET" role="form">
				<fieldset>
					<div class="control-group">
						<label for="job-search-class">Job class</label>
						<input type="text" name="class" id="job-search-class" placeholder="ClassName" value="<?php echo $searchData['class'] ?>" />
					</div>
					<div class="control-group">
						<label for="job-search-queue">Queue</label>
						<input type="text" name="queue" id="job-search-queue" placeholder="Queue name" value="<?php echo $searchData['queue'] ?>" />
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

					<label>Other</label>
					<div class="control-group">
						<input type="text" name="workers" placeholder="Worker name" value="<?php echo $searchData['workers'] ?>"/>
					</div>
				</fieldset>

				<button class="btn btn-primary" type="submit">Search</button>

			</form>
</div>



<?php

	function getFormErrors($message)
	{
		return '<span class="help-inline">' . $message . '</span>';
	}

?>