<?php
/**
 * Job distribution template
 *
 * Website jobs distribution page
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
?><div class="container" id="main">
	<div class="page-header">
		<h1>Jobs Distribution</h1>
	</div>
	<div class="row">

		<div class="span12">
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

					echo "<script type='text/javascript'>";
					echo "$(document).ready(function() { ";
						echo "pieChart('jobRepartition', " . $jobsRepartitionStats->total . ", " . json_encode($pieDatas) . ");";
					echo "})</script>";
				?>
			</div>


			<table class="table table-hover">
				<thead>
					<tr>
						<th class="span1">#</th>
						<th>Job class</th>
						<th class="stats-nb">Count</th>
						<th class="stats-nb">Distribution</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$i = 1;
					foreach ($jobsRepartitionStats->stats as $stat) {
						echo '<tr>';
						echo '<td>' . $i++ . '</td>';
						echo '<td>' . $stat['_id'] . '</td>';
						echo '<td class="stats-nb">' . number_format($stat['value']) . '</td>';
						echo '<td class="stats-nb"><div style="position:relative;">';
						echo '<span class="chart-bar" style="width:' . $stat['percentage'] . '%;"></span>';
						echo '<b>' . $stat['percentage'] . '%</b></div></div></td>';
						echo '</tr>';

					}


					if ($jobsRepartitionStats->total > 0) {
						echo '<tr class="info">';
						echo '<td></td>';
						echo '<td>Total</td>';
						echo '<td class="stats-nb">' . number_format($jobsRepartitionStats->total) . '</td>';
						echo '<td class="stats-nb">100%</td>';
						echo '</tr>';
					} else {
						echo '<tr class="info">';
						echo '<td colspan=4>No jobs found</td>';
						echo '</tr>';
					}

					?>
				</tbody>
			</table>
		</div>


</div>