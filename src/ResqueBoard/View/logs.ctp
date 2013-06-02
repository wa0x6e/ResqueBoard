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
 * @package    ResqueBoard
 * @subpackage ResqueBoard.View
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @copyright  2012-2013 Wan Qi Chen
 * @link       http://resqueboard.kamisama.me
 * @since      1.0.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
	<div class="with-sidebar" ng-controller="logActivityController">


		<div class="bloc">
			<div class="pull-right h2-btn"><button class="btn btn-mini" id="clear-log-area">Clear All</button></div>
			<h2>Latest Activities <span class="badge badge-info" data-rel="log-counter">0</span></h2>
		</div>

		<script id="log-template" type="text/x-jsrender">
			<li data-verbosity="{{>levelName}}" data-type="{{>action}}" data-worker="{{>workerClass}}">
				<span class="label {{>levelClass}}">{{>levelName}}</span>
				<em class="worker" style="color:{{>color}}"><a href="workers#{{>workerClass}}" title="{{>worker}}">{{>worker}}</a></em>
				<div class="log-message"><b class="type">{{>action}}</b> {{:detail}}</div> <time data-event="tooltip" title="{{>time}}" datetime="{{>time}}"><i class="icon-time"></i> {{>hourTime}}</time>
			</li>
		</script>

		<div class="bloc">
			<ol class="log-area" id="log-area"></ol>
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
						ucwords($info['name']).' </li>';
					}
				?>
			</ul>

			<h4>By type</h4>
			<ul class="unstyled">
				<?php
					foreach ($logTypes as $type) {
						echo '<li>'.

						ucwords($type).' <button class="btn btn-mini pull-right" data-type="'.
						$type.'" data-rel="type">Clear</button>' .
						'<span class="pull-right badge" data-rel="'.$type.'">0</span>'.
						'</li>';
					}
				?>
			</ul>
		</fieldset>
	</form>
</div>
