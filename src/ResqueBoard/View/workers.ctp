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
	listenToWorkersJob("horizontal-bar", "table");
	listenToWorkersActivities();
});
</script>

<div id="worker-form" class="modal hide"></div>


	<div class="page-header">
		<h1>Workers</h1>
	</div>

	<div class="full-width">


			<div class="bloc"><h2>Active workers <span class="badge badge-info"><?php echo count($workers) ?></span></h2></div>

			<div id="working-area" class="content-bloc">
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
				<?php ResqueBoard\Lib\WorkerHelper::renderTable($workers, $readOnly); ?>
				</tbody>
				</table>
			</div>
			<script id="working-template" type="text/x-jsrender">
				<span class="label" id="_{{>job_id}}">{{>action}}</span>
			</script>
		</div>

