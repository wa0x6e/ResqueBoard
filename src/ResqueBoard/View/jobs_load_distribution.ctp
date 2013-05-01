<?php
/**
 * Job load distribution template
 *
 * Website Job load distribution page
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
 * @since         1.3.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

$timezone = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');

?>


<div class="full-width">


		<div class="bloc">

			<form class="pull-right navigator">
				<?php
				$start = $startDate->modify('first day of this month');
				$endDate = new \DateTime();
				$endDate->modify('first day of next month');

				$dateRange = array(clone $start);
				while ($start->modify('first day of next month') < $endDate) {
					$dateRange[] = clone $start;
				}


				echo '<select class="span2">';
				$i = 0;
				foreach ($dateRange as $date) {
					echo '<option value="//'. $_SERVER['HTTP_HOST'] . '/jobs/distribution/load/' . $date->format('Y/m').'"'. ($date->format('Y/m') == $currentDate->format('Y/m') ? ' selected="selected"' : '') .'>'. $date->format('F Y') .'</option>';
				}
				echo '</select>';

				?>
			</form>
			<?php
				$date = new \DateTime($jobsMatrix[0]['time']);
				$date->setTimezone($timezone);

				echo '<h2>' . $date->format('F Y') . '</h2>';
			?>
		</div>

		<div class="ftr-bloc">
			<table class="jobs-matrix">
				<?php

				$max = 0;
				$totalMonth = 0;
				$today = new DateTime();

				foreach ($jobsMatrix as $job) {
					if ($job['value'] > $max) {
						$max = $job['value'];
					}
					$totalMonth += $job['value'];
				}

				$hours = range(0, 23);

				echo '<tr>';
				echo '<td></td>';
				foreach ($hours as $hour) {
					echo '<td class="head-hour">'.(($hour < 10) ? '0' : ''). $hour . '</td>';
				}
				echo '<td class="daily-total"></td>';

				$currentHour = 0;
				$dailyTotal = 0;



				$pivotDate = new \DateTime($jobsMatrix[count($jobsMatrix)-1]['time']);
				$pivotDate->setTimezone($timezone);

				foreach ($jobsMatrix as $matrix) :

					$date = new \DateTime($matrix['time']);
					$date->setTimezone($timezone);

					if ($pivotDate->format('d') !== $date->format('d')) {
						echo '</tr><tr class="'.
							($date->format('N') == 1 ? 'new-week' : '') .
							($date->format('Y-m-d') === $today->format('Y-m-d') ? ' today' : '') .
							($date->format('N') == 6 || $date->format('N') == 7 ? 'weekend' : '')
							.'">';
						echo '<td class="head-day">'.$date->format('D') . ' <b>' . $date->format('d').'</b></td>';
						$currentHour = 0;
						$dailyTotal = 0;
					}

					$dailyTotal += $matrix['value'];
					$quantile = $max > 0 ? ceil(round($matrix['value']/$max*100, 1)) : 0;

					if ($matrix['value'] == 0) {
						$class = 'q0';
					} else if ($quantile <= 20) {
						$class = 'q1';
					} else if ($quantile <= 40) {
						$class = 'q2';
					} else if ($quantile <= 60) {
						$class = 'q3';
					} else if ($quantile <= 80) {
						$class = 'q4';
					} else {
						$class = 'q5';
					}

					echo '<td class="'.$class. (($currentHour % 6) == 0 ? ' day-quarter' : '').'">';
					if ($matrix['value'] > 0) {
						echo '<a href="/jobs/view?date_after=' .
						$date->format('Y-m-d') . ' ' . str_pad($currentHour, 2, '0', STR_PAD_LEFT) . ':00:00&date_before=' .
						$date->format('Y-m-d') . ' ' . str_pad($currentHour, 2, '0', STR_PAD_LEFT) . ':59:59" data-event="tooltip" title="<b>' . number_format($matrix['value']) . ' jobs</b> on <br/>' . $date->format('l d M Y \a\t H\h') . '">';
					}
					echo '<i>'.$matrix['value'].'</i> ';
					if ($matrix['value'] > 0) {
						echo '</a>';
					}
					echo '</td>';

					$pivotDate = $date;
					$currentHour++;

					if ($currentHour === 24) {
						$perc = $totalMonth > 0 ? $dailyTotal/$totalMonth*100 : 0;
						echo '<td class="daily-total"><div class="padd-fixer"><span class="chart-bar" style="width:'. $perc.'%;"></span>';

						if ($perc > 0) {
							echo '<span class="perc">' . round($perc, 2) . ' %</span>';
						}
						echo ' <b>'.number_format($dailyTotal).'</b></div></td>';
					}

				endforeach;

				echo '<tr><td></td>';
				foreach ($hours as $hour) {
					echo '<td class="head-hour">'.(($hour < 10) ? '0' : ''). $hour . '</td>';
				}
				echo '<td class="daily-total"><b>Total : ' . number_format($totalMonth) . '</b></td>';

				?>
				</tr>
			</table>
		</div>

		<div class="bloc">
			<p>
			<ol class="table-legend unstyled clearfix">
				<li class="q0"></li>
				<li class="q1"></li>
				<li class="q2"></li>
				<li class="q3"></li>
				<li class="q4"></li>
				<li class="q5"></li>
			</ol>

			Scale based on current month
		</p>
		</div>

	</div>




