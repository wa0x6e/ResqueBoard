<?php
/**
 * Logs browser template
 *
 * Display old logs
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
 * @since         1.2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>

<div class="container" id="main">
	<div class="page-header">
		<h2>Logs</h2>
		<div class="btn-group btn-nav">
            <a class="btn btn-small" href="/logs">Tail latest</a>
            <a class="btn btn-small active" href="/logs/browse">Browse logs</a>
        </div>
	</div>


	<div class="row">
		<div class="span10">

			<?php
				if ($logs === null) {
					echo '<div class="knight-unit"><i class="icon-search icon"></i><h2>Log Browser</h2><p class="tagline">Search and browse logs</p></div>';


				} elseif (empty($logs)) {

					echo '<div class="alert"><i class="icon-search" style="font-size:32px; float:left;margin: 10px 10px 0 0;"></i><h4>No results found</h4>Try searchiung again :)</div>';

				} else {

			?>
			<h3>Search results <span class="badge badge-info" data-rel="log-counter"><?php echo number_format($pagination->totalResult) ?></span></h3>
			<ol id="log-area" class="infinite-scroll">
				<?php
				$pivotDate = '';
				$colors = array("#1f77b4", "#aec7e8", "#ff7f0e", "#ffbb78", "#2ca02c", "#98df8a",
					"#d62728", "#ff9896", "#9467bd", "#c5b0d5", "#8c564b", "#c49c94", "#e377c2",
					"#f7b6d2", "#7f7f7f", "#c7c7c7", "#bcbd22", "#dbdb8d #", "17becf", "#9edae5"
				);

				$colorMap = array();

				foreach($logs as $log) {
					$workerId = str_replace(array('.', ':'), '', $log['worker']);

					if ($pivotDate !== $log['date']->format('l F d Y')) {
						echo '<li class="li-head"><i class="icon-calendar"></i> '.$log['date']->format('l F d<\s\u\p>S</\s\u\p>, Y').'</li>';
						$pivotDate = $log['date']->format('l F d Y');
					}

					if (!isset($colorMap[$workerId])) {
						$colorMap[$workerId] = $colors[rand(0,19)];
					}

					?>
					<li data-verbosity="<?php echo $logLevels[$log['level']]['name'] ?>" data-type="<?php echo $log['event_type']; ?>" data-worker="<?php echo $workerId ?>">
					<div class="label-c"><span class="label <?php echo $logLevels[$log['level']]['class'] ?>"><?php echo $logLevels[$log['level']]['name'] ?></span></div>
					<em class="worker" style="color:<?php echo $colorMap[$workerId] ?>"><?php echo $log['worker'] ?></em>
					<b class="type"><?php echo $log['event_type']; ?></b> <?php
						if (isset($log['job_id'])) {
							echo 'Job <a href="/jobs/view?job_id='.$log['job_id'].'" rel="contents" title="View job details">#' . $log['job_id'] . '</a>';
						} else {
							echo 'Worker #' . $log['worker'];
						}
					?> <time title="<?php echo $log['date']->format('c') ?>" datetime="<?php echo $log['date']->format('c') ?>"><i class="icon-time"></i> <?php echo $log['date']->format('H:i:s') ?></time>
					</li>

					<?php
				}?>

			</ol>

			<?php if (isset($pagination)) {
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
							Page <?php echo $pagination->current?> of <?php echo max(number_format($pagination->totalPage), 1) ?>, found <?php echo number_format($pagination->totalResult) ?> entries
						</li>
						<li class="next<?php if ($pagination->current >= $pagination->totalPage) {
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
		?>

		</div>



		<div class="span2">
			<form class="" id="log-browser-form">
				<fieldset><legend>Verbosity</legend>
				<select class="span2" name="event_level[]" multiple="" size="<?php echo count($logLevels)?>">
					<?php
						foreach ($logLevels as $levelCode => $info) {

							echo '<option value="'.$levelCode.'"';
							if (in_array($levelCode, (array)$searchData['event_level'])) {
								echo ' selected="selected"';
							}
							echo '>'. ucwords($info['name']).'</option>';

						}
					?>
					</select>
				</fieldset>

				<fieldset><legend>Type</legend>
				<select class="span2" name="event_type">
					<?php
						foreach ($logTypes as $levelName) {

							echo '<option value="'.$levelName.'"';
							if ($levelName == $searchData['event_type']) {
								echo ' selected="selected"';
							}
 							echo '>'. ucwords($levelName).'</option>';

						}
					?>
					</select>
				</fieldset>

				<fieldset><legend>Date</legend></fieldset>

				<label>Start Date
					<input type="text" name="date_after" class="span2" value="<?php echo $searchData['date_after'] ?>" placeholder="YYYY-MM-DD hh:mm:ss" />
				</label>

				<label>End Date
					<input type="text" name="date_before" class="span2" value="<?php echo $searchData['date_before'] ?>" placeholder="YYYY-MM-DD hh:mm:ss" />
				</label>

				<button type="submit" class="btn btn-block btn-primary">Search</button>

			</form>
		</div>
	</div>

</div>