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



	<div class="with-sidebar">


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

<div class="sidebar">
	<div class="bloc">
	<h3>Listen to</h3>
	<form class="" id="log-filter-form">
		<fieldset>
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

	<h3>Clear screen</h3>
	<form id="log-sweeper-form">

		<fieldset>
			<h4>By verbosity</h4>
			<ul class="unstyled">
				<?php
					foreach ($logLevels as $code => $info) {
						echo '<li>'.
						' <button class="btn btn-mini pull-right" data-level="'.
						$info['name'].'" data-rel="verbosity">Clear</button>'.
						'<span class="pull-right badge '.$info['class'].'" data-rel="'.$info['name'].'">0</span>'.
						$info['name'].' </li>';
					}
				?>
			</ul>

			<h4>By type</h4>
			<ul class="unstyled">
				<?php
					foreach ($logTypes as $type) {
						echo '<li>'.

						$type.' <button class="btn btn-mini pull-right" data-type="'.
						$type.'" data-rel="type">Clear</button>' .
						'<span class="pull-right badge" data-rel="'.$type.'">0</span>'.
						'</li>';
					}
				?>
			</ul>
		</fieldset>
	</form>
</div>
