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

	<div id="jobs-activities-graph"></div>

	<div class="row">
	<div class="bloc">
		<div class="span5">
			<h2><a href="/jobs/distribution/class" title="View jobs distribution by classes">Distribution by classes</a></h2>

			<div id="jobRepartition">
				<?php
					$pieDatas = array();
					$total = 100;
					foreach($jobsRepartitionStats->stats as $stat) {
						if ($stat['percentage'] >= 15) {
							$pieDatas[] = array('name' => $stat['_id'], 'count' => $stat['percentage']);
							$total -= $stat['percentage'];
						}
					}
					if (count($pieDatas) < count($jobsRepartitionStats->stats)) {
						$pieDatas[] = array('name' => 'Other', 'count' => $total);
					}

					//$diff = $jobsStats->oldest === null ? 0 : date_diff($jobsStats->oldest, new DateTime())->format('%a');
					//$jobsDailyAverage = empty($diff) ? 0 : round($jobsStats->total / $diff);

					echo "<script type='text/javascript'>";
					echo "$(document).ready(function() { ";
						echo "pieChart('jobRepartition', " . $jobsRepartitionStats->total . ", " . json_encode($pieDatas) . ");";
					echo "})</script>";
				?>
			</div>

			<table class="table table-condensed table-hover table-greyed">
				<thead>
					<tr>
						<th class="name">Job class</th>
						<th>Count</th>
						<th>Distribution</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$total = 0;
					foreach ($jobsRepartitionStats->stats as $stat) {
						echo '<tr>';
						echo '<td class="name">' . $stat['_id'] . '</td>';
						echo '<td>' . number_format($stat['value']) . '</td>';
						echo '<td><div style="position:relative;">';
						echo '<span class="chart-bar" style="width:' . $stat['percentage'] . '%;"></span>';
						echo '<b>' . ($stat['percentage'] != 0 ? '' : '~') . $stat['percentage'] . '%</b></div></div></td>';
						echo '</tr>';

						$total += $stat['value'];
					}

					if ($total < $jobsRepartitionStats->total) {
						$p = round(($jobsRepartitionStats->total - $total) / $jobsRepartitionStats->total * 100, 2);

						echo '<tr>';
						echo '<td class="name">Other</td>';
						echo '<td>' . number_format($jobsRepartitionStats->total - $total) . '</td>';
						echo '<td><div style="position:relative;">';
						echo '<span class="chart-bar" style="width:' . $p . '%;"></span>';
						echo '<b>' . ($p != 0 ? '' : '~') . $p . '%</b></div></div></td>';
						echo '</tr>';
					}

					if ($jobsRepartitionStats->total > 0) {
						echo '<tr class="info">';
						echo '<td>Total</td>';
						echo '<td>' . number_format($jobsRepartitionStats->total) . '</td>';
						echo '<td>100%</td>';
						echo '</tr>';
					} else {
						echo '<tr class="info">';
						echo '<td colspan=3>No jobs found</td>';
						echo '</tr>';
					}

					?>
				</tbody>
			</table>
			<a href="jobs/distribution">View all</a>
		</div>

		<div class="span6">
			<ng-include src="'/partials/queues-table.html'" ng-cloak></ng-include>
			<ng-include src="'/partials/latest-jobs-heatmap.html'" ng-cloak></ng-include>
		</div>

	</div>
</div>

</div>
