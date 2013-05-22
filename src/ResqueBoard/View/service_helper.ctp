<?php
/**
 * Header template
 *
 * Included before each template
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
<div id="service-debugger">
	<div class="debugger-content">
<?php

	foreach(ResqueBoard\Lib\Service\Service::$services as $name => $service) {
		$logs = $service::getLogs();

		echo '<h3>' . $name . '</h3>';

		echo '<table class="table table-condensed">';
		echo '<tr><th>Query</th><th>Took (ms)</th></tr>';
		foreach($logs['logs'] as $log) {
			echo '<tr>';
			echo '<td><code>' .$log['command'] . '</code><small>From ' . $log['trace']['file'] . ' @ ' . $log['trace']['line'] . '</small></td>';
			echo '<td>' . $log['time'] . '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
?>
	</div>
</div>