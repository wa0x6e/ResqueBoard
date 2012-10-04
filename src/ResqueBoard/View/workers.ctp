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
				<?php ResqueBoard\Lib\WorkerHelper::renderTable($workers, $readOnly); ?>
				</tbody>
				</table>
			</div>
			<script id="working-template" type="text/x-jsrender">
				<span class="label" id="_{{>job_id}}">{{>action}}</span>
			</script>



		</div>
	</div>

</div>