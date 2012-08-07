<script type="text/javascript">
$(document).ready(function() {
    listenToWorkersJob();
});
</script>
<div class="container" id="main">
	<div class="page-header">
		<h2>Working</h2>
	</div>
	<div class="row">
		<div class="span12">
			<div class="alert alert-block alert-info">
				<h4 class="alert-heading">Description</h4>
				Displays jobs the workers are currently working on
			</div>

			<div id="working-area">
				<table class="table table-bordered">
				<thead>
					<tr>
						<th class="worker-name">Worker</th>
						<th>Processed</th>
						<th>Failed</th>
						<th>Activities</th>
					</tr>
				</thead>
				<tbody>
				<?php
				
				    $totalJobs = 0;
				    array_walk($workers, function($q) use (&$totalJobs) {$totalJobs += $q['processed'];});
			
					foreach ($workers as $worker) {

					    $barWidth = $totalJobs != 0 ? (($worker['processed']/$totalJobs) * 100)  : 0;
					    
						$workerId = str_replace('.', '', $worker['host']) . $worker['process'];
						echo '<tr>';
						echo '<td><h4>' . $worker['host'] . ':' . $worker['process']. '</h4>';
						echo '<small class="queues-list"><strong><i class="icon-list-alt"></i> Queues : </strong>';
						array_walk($worker['queues'], function($q){echo '<span class="queue-name">'.$q.'</span> ';});
						echo '</small></td>';
						echo '<td class="stats-number" rel="processed" id="s_'.$workerId.'">' .
						'<span class="chart-bar" style="width:'.$barWidth.'%;"></span>'.
						'<b>'.$worker['processed'] . '</b></td>';
						echo '<td class="stats-number" rel="failed" id="f_'.$workerId.'">'.$worker['failed'] . '</td>';
						echo '<td id="'.$workerId.'"></td>';
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