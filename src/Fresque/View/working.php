<script type="text/javascript" src="js/app.js"></script>

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
						<th>Success</th>
						<th>Failed</th>
						<th>Activities</th>
					</tr>
				</thead>
				<tbody>
				<?php
					foreach ($workers as $worker) {

						$workerId = str_replace('.', '', $worker['host']) . $worker['process'];
						echo '<tr>';
						echo '<td>' . $worker['host'] . ':' . $worker['process'] . '</td>';
						echo '<td class="stats-number" id="s_'.$workerId.'">'.$worker['processed'] .'</td>';
						echo '<td class="stats-number" id="f_'.$workerId.'">'.$worker['failed'] . '</td>';
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

			<script type="text/javascript">
			$(document).ready(function() {

				listenToWorkersJob();
			});

			

			</script>
			
		</div>
	</div>
	
</div>