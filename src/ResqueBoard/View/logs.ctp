<?php
/**
 * Logs template
 *
 * Website logs page
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
<script type="text/javascript">
	$(document).ready(function() {
		loadLogs();
	});
</script>

	<div class="page-header">
		<h1>Logs</h1>
	</div>


	<div class="row">
		<div class="span8">

			<div class="bloc">
				<div class="pull-right"><button class="btn btn-mini" id="clear-log-area">Clear All</button></div>
				<h2>Latest Activities <span class="badge badge-info" data-rel="log-counter">0</span></h2>
			</div>

			<script id="log-template" type="text/x-jsrender">
				<li data-verbosity="{{>levelName}}" data-type="{{>action}}" data-worker="{{>workerClass}}">
					<div class="label-c"><span class="label {{>levelClass}}">{{>levelName}}</span></div>
					<a href="/workers#{{>workerClass}}"><em class="worker" style="color:{{>color}}">{{>worker}}</em></a>
					<b class="type">{{>action}}</b> {{:detail}} <time data-event="tooltip" title="{{>time}}" datetime="{{>time}}">{{>relativeTime}}</time>
				</li>
			</script>

			<div class="content-bloc">
				<ol id="log-area"></ol>
			</div>

		</div>

	</div>

</div>

<div class="sidebar">
	<form class="" id="log-filter-form">
		<fieldset><legend>Listen to</legend>
			<?php
				foreach ($logLevels as $code => $info) {

					echo '<label class="checkbox"><input type="checkbox" data-rel="'.
					$info['name'].'"';

					if (!in_array($info['name'], $mutedLevels)) {
						echo ' checked=""';
					}

					echo '> '.ucwords($info['name']).'</label>';

				}
			?>
		</fieldset>
	</form>

	<form id="log-sweeper-form">
		<fieldset><legend>Clear</legend>
			<h4 class="sep">By verbosity</h4>
			<ul class="unstyled">
				<?php
					foreach ($logLevels as $code => $info) {
						echo '<li><span class="badge '.$info['class'].'" data-rel="'.$info['name'].'">0</span> '.
						$info['name'].' <button class="btn btn-mini pull-right" data-level="'.
						$info['name'].'" data-rel="verbosity">Clear</button></li>';
					}
				?>
			</ul>

			<h4 class="sep">By type</h4>
			<ul class="unstyled">
				<?php
					foreach ($logTypes as $type) {
						echo '<li><span class="badge" data-rel="'.$type.'">0</span> '.
						$type.' <button class="btn btn-mini pull-right" data-type="'.
						$type.'" data-rel="type">Clear</button></li>';
					}
				?>
			</ul>
		</fieldset>
	</form>

	<div class="alert alert-info">
		Clear action just clear lines from the display, and does not delete them from the database
	</div>


