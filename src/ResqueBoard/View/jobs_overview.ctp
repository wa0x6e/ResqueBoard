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

?><div class="container" id="main">
	<div class="page-header">
		<h1>Jobs overview</h1>
	</div>

	<script type="text/javascript">
	$(document).ready(function() {
		initJobsOverview();
	});
	</script>

	<div class="row">
		<div class="span12">

		    <div class="btn-group pull-right" id="date-range">
		    	<?php
		    		foreach ($ranges as $range => $info) {
		    			echo '<a
		    			href="/jobs/overview/' . $range . '/' . $uriDate->format('Y-m-d\TH:i:s\Z') . '"
		    			class="btn' . ($currentRange !== $range ? '' : ' active') . '"
		    			data-start-date="' . $info['start']->format('Y-m-d\TH:i:s') . '"
		    			data-end-date="' . $info['end']->format('Y-m-d\TH:i:s') . '"
		    			data-step="' . $info['step'] . '">' . $range . '</a>';
		    		}
		    	?>
		    </div>

		    <h2><?php

		    switch($currentRange) {
		    	case 'hour' :
		    		echo $startDate->format('d F Y, H:i');
		    		echo '-', ($startDate->format('H') + 1), ':00';
		    		break;
	    		case 'day' :
	    			echo $startDate->format('d F Y');
	    			break;
	    		case 'week' :
	    			echo $startDate->format('d M Y');
	    			echo $endDate->format(' - d M Y');
	    			echo ' <small>Week #' . $startDate->format('W') .'</small>';
	    			break;
	    		case 'month' :
	    			echo $startDate->format('d M Y');
	    			echo $endDate->format(' - d M Y');
		    		break;
		    }

		    ?></h2>

		    <div class="row">
		    	<div id="chart" class="span12"></div>
			</div>

			<ul class="jobs-stats unstyled clearfix" id="type-range">
				<li><div>
					<a class="active" href="" data-type="got" data-start-date="<?php echo $ranges[$currentRange]['start']->format('Y-m-d\TH:i:s'); ?>" data-end-date="<?php echo $ranges[$currentRange]['end']->format('Y-m-d\TH:i:s'); ?>" data-step="<?php echo $ranges[$currentRange]['step']; ?>"><strong><?php echo number_format($jobsStats->total) ?></strong>Processed Jobs</a>


				</div></li>
				<li><div><a href="" data-type="fail" data-start-date="<?php echo $ranges[$currentRange]['start']->format('Y-m-d\TH:i:s'); ?>" data-end-date="<?php echo $ranges[$currentRange]['end']->format('Y-m-d\TH:i:s'); ?>" data-step="<?php echo $ranges[$currentRange]['step']; ?>"><strong><?php echo $jobsStats->perc[ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED] ?> %</strong> <?php echo number_format($jobsStats->count[ResqueBoard\Lib\ResqueStat::JOB_STATUS_FAILED]) ?> failed Jobs</a></div></li>

			</ul>

		</div>
	</div>

</div>
