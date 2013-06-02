<?php
/**
 * job template
 *
 * Website jobs page
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

	<?php \ResqueBoard\Lib\PageHelper::renderJobStats($stats); ?>

	<div id="jobs-activities-graph"></div>

	<div class="row">
	<div class="bloc">
		<div class="span5">
			<h2><a href="jobs/distribution/class" title="View jobs distribution by classes">Jobs distribution by classes</a></h2>
			<ng-include src="'partials/class-distribution.html'" ng-init="class = 'table-condensed table-greyed'; limit = 15" ></ng-include>
			<a href="jobs/distribution/class">View all</a>
		</div>

		<div class="span6">
			<ng-include src="'partials/queues-table.html'" ng-cloak></ng-include>
			<ng-include src="'partials/latest-jobs-heatmap.html'" ng-cloak></ng-include>
		</div>

	</div>
</div>

</div>