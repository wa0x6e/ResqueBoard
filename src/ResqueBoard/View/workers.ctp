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

<div id="worker-form" class="modal hide"></div>

<div class="full-width" ng-controller="WorkersCtrl" ng-cloak>

    <div class="knight-unit" ng-show="length==0"><i class="icon-cogs icon"></i><h2>No active workers</h2></div>

	<div class="ftr-bloc" ng-hide="length==0">
		<h2>Active workers <span class="badge badge-info">{{length}}</span></h2>

		<table class="table workers-table">
		<thead>
			<tr>
				<th>Worker</th>
				<th>Processed</th>
				<th>Failed</th>
				<th style="width:450px;">Activities</th>
			</tr>
		</thead>
		<tbody>
			<tr class="worker-stats" ng-repeat="worker in workers" ng-class="{paused: !worker.active}">
            <td>
                <div class="btn-group pull-right">
                    <a class="btn btn-small dropdown-toggle btn-discret" data-toggle="dropdown" href="#">
                        <i class="icon-cog"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
	                        <a href="#" ng-show="worker.active" title="Pause worker" ng-click="pause(worker.id, $event)">
		                        <i class="icon-pause"></i> Pause worker
		                    </a>
	                	</li>
	                	<li ng-hide="worker.active">
	                        <a href="#" title="Resume worker" ng-click="resume(worker.id, $event)">
		                        <i class="icon-play"></i> Resume worker
		                    </a>
	                	</li>
	                    <li>
                            <a href="#" title="Stop worker" ng-click="stop(worker.id, $event)">
                            <i class="icon-off"></i> Stop worker</a>
                        </li>
                    </ul>
                </div>
            <h4>{{worker.host}}:{{worker.process}}</h4>
            <small><strong><i class="icon-list-alt"></i> Queues : </strong>
				<span class="queue-name" ng-repeat="queue in worker.queues">{{queue}}</span>
            </small></td>
            <td class="stats-number inner-wrap"><div class="padd-fixer">
            <span class="chart-bar" style="width:{{worker.stats.jobperc}}%;"></span>
            <b>{{worker.stats.processed}}</b></div></td>
            <td class="stats-number inner-wrap"><div class="padd-fixer"><b>{{worker.stats.failed}}</b></div></td>
            <td class="inner-wrap" ng-switch on="$index">
            	<div class="padd-fixer" ng-switch-when="0">
                    <graph-horizon-chart class="graph"  workers="workers" length="length"></graph-horizon-chart>
                </div>
            </td>
            </tr>
		</tbody>
		</table>

    </div>
</div>