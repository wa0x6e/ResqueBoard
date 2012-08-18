<?php
/**
 * Workers template
 *
 * Website workers page
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
	listenToWorkersJob("horizontal-bar");
	listenToWorkersActivities();
});
</script>
<div class="container" id="main">
	<div class="page-header">
		<h2>Workers <small class="subtitle">Active Workers activities</small></h2>
	</div>
	<div class="row">
		<div class="span12">


			<div id="working-area">
				<table class="table table-bordered">
				<thead>
					<tr>
						<th>Worker</th>
						<th>Processed</th>
						<th>Failed</th>
						<th style="width:450px;">Activities</th>
					</tr>
				</thead>
				<tbody>
				<?php

					$totalJobs = 0;
					$i = 0;
					array_walk(
						$workers,
						function ($q) use (&$totalJobs) {
							$totalJobs += $q['processed'];
						}
					);

					foreach ($workers as $worker) {

						$barWidth = $totalJobs != 0 ? (($worker['processed']/$totalJobs) * 100)  : 0;

						$workerId = str_replace('.', '', $worker['host']) . $worker['process'];
						echo '<tr class="worker-stats" id="'.$workerId.'">';
						echo '<td><h4>' . $worker['host'] . ':' . $worker['process']. '</h4>';
						echo '<small class="queues-list"><strong><i class="icon-list-alt"></i> Queues : </strong>';
						array_walk(
							$worker['queues'],
							function($q){
								echo '<span class="queue-name">'.$q.'</span> ';
							}
						);
						echo '</small></td>';
						echo '<td class="stats-number inner-wrap"><div style="position:relative;">' .
						'<span class="chart-bar" data-type="chart" data-chart-type="horizontal-bar" style="width:'.$barWidth.'%;"></span>'.
						'<b data-status="processed">'.number_format($worker['processed']) . '</b></div></td>';
						echo '<td class="stats-number inner-wrap"><div style="position:relative;"><b data-status="failed">'.number_format($worker['failed']) . '</b></div></td>';
						echo '<td class="inner-wrap">';
						if ($i++ == 0) {
							echo '<div style="position:relative;"><div id="worker-activities"></div></div>';
						}
						echo '</td>';
						echo '</tr>';
					}
				?>
				</tbody>
				</table>
			</div>
			<script id="working-template" type="text/x-jsrender">
				<span class="label" id="_{{>job_id}}">{{>action}}</span>
			</script>



		</div>
	</div>

</div>