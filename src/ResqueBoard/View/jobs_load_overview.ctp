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


function generateOptionsList($range, $start)
{


	switch ($range) {
		case 'hour' :
			echo '<div class="span1"><label>Day';
			echo '<select class="span1" name="range-day">';
			$days = range(1, 31);
			foreach ($days as $day) {
				echo '<option value="'.$day.'"' . ($day == $start->format('j') ? ' selected=selected' : '') .'>'.$day.'</option>';
			}
			echo '</select></label></div>';

			echo '<div class="span1"><label>Month';
			echo '<select class="span1" name="range-month">';
			$months = range(1, 12);
			foreach ($months as $month) {
				$date = new DateTime('2000-' . $month . '-01');
				echo '<option value="'.$month.'"' . ($month == $start->format('n') ? ' selected=selected' : '') .'>'.$date->format('M').'</option>';
			}
			echo '</select></label></div>';

			echo '<div class="span1"><label>Year';
			echo '<input name="range-year" class="span1" type="text" value="'.$start->format('Y').'" /></label></div>';

			echo '<div class="span1"><label>Hour';
			echo '<select class="span1" name="range-hour">';
			$hours = range(0, 23);
			foreach ($hours as $hour) {
				echo '<option value="'.$hour.'"' . ($hour == $start->format('G') ? ' selected=selected' : '') .'>'. ($hour < 10 ? '0' : '') . $hour.'</option>';
			}
			echo '</select></label></div>';

			echo '<button class="btn btn-success padded" data-range="'.$range.'"><i class="fa fa-chevron-right"></i></button>';

			echo '<li>';


			break;

		case 'day' :

			echo '<div class="span1"><label>Day';
			echo '<select class="span1" name="range-day">';
			$days = range(1, 31);
			foreach ($days as $day) {
				echo '<option value="'.$day.'"' . ($day == $start->format('j') ? ' selected=selected' : '') .'>'.$day.'</option>';
			}
			echo '</select></label></div>';

			echo '<div class="span1"><label>Month';
			echo '<select class="span1" name="range-month">';
			$months = range(1, 12);
			foreach ($months as $month) {
				$date = new DateTime('2000-' . $month . '-01');
				echo '<option value="'.$month.'"' . ($month == $start->format('n') ? ' selected=selected' : '') .'>'.$date->format('M').'</option>';
			}
			echo '</select></label></div>';

			echo '<div class="span1"><label>Year';
			echo '<input name="range-year" class="span1" type="text" value="'.$start->format('Y').'" /></label></div>';

			echo '<button class="btn btn-success padded" data-range="'.$range.'"><i class="fa fa-chevron-right"></i></button>';

			break;

		case 'week' :
			$date  = new DateTime();
			$week = ResqueBoard\Lib\DateHelper::getStartWeek($date->setDate($date->format('Y'), 1, 1)->setTime(0, 0, 0));

			echo '<div class="span3"><label>Week #';
			echo '<select class="span3 name="range-date"">';

			do {
				echo '<option value="'.$week->format('c').'"' . ($week->format('W') === $start->format('W') ? ' selected=selected' : '') .'>Week #'.$week->format('W | d M - ') . ResqueBoard\Lib\DateHelper::getEndWeek($week)->format('d M') . '</option>';
				$week->modify('+1 week');
			} while ($week->format('W') > 1);

			echo '</select></label></div>';

			echo '<button class="btn btn-success padded" data-range="'.$range.'"><i class="fa fa-chevron-right"></i></button>';

			break;

		case 'month' :

			echo '<div class="span2"><label>Month';
			echo '<select class="span2" name="range-month">';
			$months = range(1, 12);
			foreach ($months as $month) {
				$date = new DateTime('2000-' . $month . '-01');
				echo '<option value="'.$month.'"' . ($month == $start->format('n') ? ' selected=selected' : '') .'>'.$date->format('F').'</option>';
			}
			echo '</select></label></div>';

			echo '<div class="span1"><label>Year';
			echo '<input name="range-year" class="span1" type="text" value="'.$start->format('Y').'" /></label></div>';

			echo '<button class="btn btn-success padded" data-range="'.$range.'"><i class="fa fa-chevron-right"></i></button>';

			break;
	}

}

function formatDate($range, $start)
{
	switch ($range) {
		case 'hour':
			return $start->format('H') . ':00 - ' . ($start->format('H') + 1) . ':00';
		case 'day':
			return $start->format('l d F Y');
		case 'week':

			return
				ResqueBoard\Lib\DateHelper::getStartWeek($start)->format('d M Y') .
				ResqueBoard\Lib\DateHelper::getEndWeek($start)->format(' - d M Y');
		case 'month':
			return $start->format('F Y');
	}
}


$headers = array(
	'hour' => array(
		'h1' => formatDate($currentRange, $startDate),
		'h2' => $startDate->format('l d F Y')
	),
	'day' => array(
		'h1' => formatDate($currentRange, $startDate)
	),
	'week' => array(
		'h1' => formatDate($currentRange, $startDate),
		'h2' => 'Week #' . $startDate->format('W')
	),
	'month' => array(
		'h1' => formatDate($currentRange, $startDate)
	)
);


?>
	<div class="full-width" ng-controller="loadOverviewController">
		    <ul class="nav nav-tabs page-nav-tab" id="date-range">
		    	<?php
		    		foreach ($ranges as $range => $info) {
		    			echo '<li class="dropdown ' . ($currentRange !== $range ? '' : ' active') . '">';
		    			echo '<a
		    			href="jobs/overview/' . $range . '/' . $uriDate->format('c') . '"
		    			data-target="#"
		    			class="dropdown-toggle" data-toggle="dropdown"
		    			data-start-date="' . $info['start']->format('c') . '"
		    			data-end-date="' . $info['end']->format('c') . '"
		    			data-step="' . $info['step'] . '">' . $range . ' <b class="caret"></b></a>';
		    			echo '<ul class="dropdown-menu span5 styled">';
		    				echo '<li>';


		    					echo '<a href="jobs/overview/' . $range . '/' . urlencode(ResqueBoard\Lib\DateHelper::{'getStart'.$range}(new DateTime())->format('c')) . '">';

		    					echo '<small class="pull-right">'. formatDate($range, new DateTime()) .'</small>';

		    					if ($range === 'day') {
		    						echo 'Today';
		    					} else {
		    						echo 'This '. $range;
		    					}



		    					echo '</a><li>';
		    					echo '<li class="divider"></li>';
		    					echo '<li><form class="form-inline dropdown-form date-range-form" action="/jobs/overview/"><div class="controls controls-row">';
		    					generateOptionsList($range, $uriDate);
		    					echo '</div></form></li>';


		    					echo '<li class="divider"></li><li class="btn-links">';
		    					echo '<a href="jobs/overview/' . $range . '/' . ResqueBoard\Lib\DateHelper::{'getStart' . $range}($uriDate, '+1')->format('c') . '" class="btn pull-right">Next '.$range.' <i class="fa fa-chevron-right"></i></a>';
								echo '<a href="jobs/overview/' . $range . '/' . ResqueBoard\Lib\DateHelper::{'getStart' . $range}($uriDate, '-1')->format('c') . '" class="btn pull-left"><i class="fa fa-chevron-left"></i> Previous '.$range.'</a>';
		    					echo '</li>';

		    				echo '</li>';
						echo '</ul>';

		    			echo '</li>';
		    		}
		    	?>
		    </ul>

		    <div class="ftr-bloc">

		    <div class="domain-nav pull-right">
		    	<?php
		    		echo '<a href="jobs/overview/' . $currentRange . '/' . urlencode(ResqueBoard\Lib\DateHelper::{'getStart' . $currentRange}($uriDate, '-1')->format('c')) . '" data-event="tooltip" rel="prev" title="Previous '.$currentRange.'"><i class="fa fa-chevron-left"></i></a>';

		    		echo '<a href="jobs/overview/' . $currentRange . '/' . urlencode(ResqueBoard\Lib\DateHelper::{'getStart' . $currentRange}($uriDate, '+1')->format('c')) . '" data-event="tooltip" rel="next"title="Next '.$currentRange.'"><i class="fa fa-chevron-right"></i></a>';

		    	?>
		    </div>

		    <h2><?php
		    	echo $headers[$currentRange]['h1'];
		    	if (!empty($headers[$currentRange]['h2'])) {
		    		echo '<small class="subtitle">' . $headers[$currentRange]['h2'] . '</small>';
		    	}
		    	?></h2>


		    	<div id="chart"></div>

			</div>

			<ul class="stats unstyled clearfix" id="type-range">
				<li><div>

					<a class="active" href="#" data-type="processed" data-expression="sum(got)" data-axis="left" data-start-date="<?php echo $ranges[$currentRange]['start']->format('c'); ?>" data-end-date="<?php echo $ranges[$currentRange]['end']->format('c'); ?>" data-step="<?php echo $ranges[$currentRange]['step']; ?>">
						<i class="pull-right fa fa-check"></i>
						<strong><?php echo number_format($jobsStats->total) ?></strong>Processed Jobs</a>


				</div></li>
				<li><div>

					<a href="#" data-type="fail" data-expression="sum(fail)" data-axis="left" data-start-date="<?php echo $ranges[$currentRange]['start']->format('c'); ?>" data-end-date="<?php echo $ranges[$currentRange]['end']->format('c'); ?>" data-step="<?php echo $ranges[$currentRange]['step']; ?>"><i class="pull-right fa fa-check-empty"></i>
						<strong><?php echo $jobsStats->perc[ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED] ?> %</strong> <?php echo number_format($jobsStats->count[ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED]) ?> failed Jobs</a>
				</div></li>
				<li><div>
					<a href="#" data-type="scheduled" data-expression="sum(movescheduled)" data-axis="left" data-start-date="<?php echo $ranges[$currentRange]['start']->format('c'); ?>" data-end-date="<?php echo $ranges[$currentRange]['end']->format('c'); ?>" data-step="<?php echo $ranges[$currentRange]['step']; ?>">
						<i class="pull-right fa fa-check-empty"></i>
						<strong><?php echo number_format($jobsStats->perc[ResqueBoard\Lib\ResqueStat::JOB_STATUS_SCHEDULED]) ?> %</strong><?php echo number_format($jobsStats->count[ResqueBoard\Lib\ResqueStat::JOB_STATUS_SCHEDULED]) ?> Scheduled Jobs</a>
				</div></li>
				<li><div>

					<a href="#" data-type="total-process-time" data-expression="sum(done(time))" data-axis="right" data-start-date="<?php echo $ranges[$currentRange]['start']->format('c'); ?>" data-end-date="<?php echo $ranges[$currentRange]['end']->format('c'); ?>" data-step="<?php echo $ranges[$currentRange]['step']; ?>"><i class="pull-right fa fa-check-empty"></i>
						<strong><?php echo ResqueBoard\Lib\DateHelper::humanize($totalProcessTime); ?></strong> Total processing time</a></div></li>

				<li><div class="static"><!--<a href="" data-type="average-process-time" data-expression="avg(done(time))" data-axis="right" data-start-date="<?php echo $ranges[$currentRange]['start']->format('c'); ?>" data-end-date="<?php echo $ranges[$currentRange]['end']->format('c'); ?>" data-step="<?php echo $ranges[$currentRange]['step']; ?>">--><strong><?php
				echo round($averageProcessTime);

				?> ms</strong> Average processing time<!--</a>--></div></li>

			</ul>


	</div>
