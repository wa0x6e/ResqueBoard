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
 * @package    ResqueBoard
 * @subpackage ResqueBoard.View
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @copyright  2012-2013 Wan Qi Chen
 * @link       http://resqueboard.kamisama.me
 * @since      1.0.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<div id="service-debugger">
	<div class="debugger-content">
<?php
	if (empty(ResqueBoard\Lib\Service\Service::$services)) {
		echo '<h3>No service activities</h3>';
	} else {

		foreach(ResqueBoard\Lib\Service\Service::$services as $name => $service) {
			$logs = $service::getLogs();

			echo '<h3>' . $name . '</h3>';

			echo '<table class="table table-condensed">';
			echo '<tr><th>Query</th><th>Took (ms)</th></tr>';
			foreach($logs['logs'] as $log) {
				echo '<tr>';
				echo '<td>';
				if (is_string($log['command'])) {
					echo '<code>' . $log['command'] . '</code>';
				} else {
					foreach($log['command'] as $command => $subCommand) {
						echo '<code>' . $command . '</code>';

						if (!empty($subCommand)) {
							echo '<ul class="unstyled">';
							foreach ($subCommand as $c) {
								echo '<li><code>' . $c[0] . ' ' . $c[1] . '</code></li>';
							}
							echo '</ul>';
						}
					}
				}

				echo '<small>From ' . $log['trace']['file'] . ' @ ' . $log['trace']['line'] . '</small></td>';
				echo '<td>' . $log['time'] . '</td>';
				echo '</tr>';
			}
			echo '</table>';
		}
	}
?>
	</div>
</div>