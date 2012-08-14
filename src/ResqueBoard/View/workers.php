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
						<th class="worker-name">Worker</th>
						<th>Processed</th>
						<th>Failed</th>
						<th style="width:800px">Activities</th>
					</tr>
				</thead>
				<tbody>
				<?php
				
				    $totalJobs = 0;
				    $i = 0;
				    array_walk($workers, function($q) use (&$totalJobs) {$totalJobs += $q['processed'];});
			
					foreach ($workers as $worker) {

					    $barWidth = $totalJobs != 0 ? (($worker['processed']/$totalJobs) * 100)  : 0;
					    
						$workerId = str_replace('.', '', $worker['host']) . $worker['process'];
						echo '<tr class="worker-stats" id="'.$workerId.'">';
						echo '<td><h4>' . $worker['host'] . ':' . $worker['process']. '</h4>';
						echo '<small class="queues-list"><strong><i class="icon-list-alt"></i> Queues : </strong>';
						array_walk($worker['queues'], function($q){echo '<span class="queue-name">'.$q.'</span> ';});
						echo '</small></td>';
						echo '<td class="stats-number">' .
						'<span class="chart-bar" rel="chart" data-chart-type="horizontal-bar" style="width:'.$barWidth.'%;"></span>'.
						'<b rel="processed">'.number_format($worker['processed']) . '</b></td>';
						echo '<td class="stats-number"><b rel="failed">'.number_format($worker['failed']) . '</b></td>';
						echo '<td>';
						if ($i++ == 0) echo '<div id="worker-activities"></div>';
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