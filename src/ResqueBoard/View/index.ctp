<?php
/**
 * Index template
 *
 * Website home page
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

<?php \ResqueBoard\Lib\PageHelper::renderJobStats($stats); ?>

<div class="ftr-bloc" ng-controller="LatestJobsGraphCtrl">
	<h3>Last activities</h3>
	<div id="lastest-jobs"></div>
	<div id="job-details" class="modal hide">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">×</button>
			<h3>Jobs <span class="badge badge-info">{{jobs.length}}</span></h3>
		</div>
		<div class="modal-body">
			<ng-include src="'/partials/jobs-list.html'"></ng-include>
		</div>
	</div>
</div>

<div class="span5">
 	<ng-include src="'/partials/workers-list.html'" ng-cloak></ng-include>
</div>

<div class="span6">
 	<ng-include src="'/partials/queues-table.html'"ng-cloak></ng-include>
	<ng-include src="'/partials/latest-jobs-heatmap.html'" ng-cloak></ng-include>
</div>