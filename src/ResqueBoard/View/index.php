<div class="container" id="main">
			
			<script id="jobs-tpl" type="text/x-jsrender">
				         <li class="accordion-group">
					        <div class="accordion-heading" data-toggle="collapse" data-target="#{{>job_id}}">
                                <div class="accordion-toggle">
                                    
                                    <span class="label label-info pull-right">{{>worker}}</span>
                                    <h4>#{{>job_id}}</h4>
                                    <small>Waiting for <code>{{>class}}</code> in <span class="label label-success">{{>queue}}</span></small>
                                </div>
                            </div>
                            <div class="collapse accordion-body" id="{{>job_id}}">
                           <div class="accordion-inner">
                            <pre class="">{{>args}}</pre></div></div>
				        </li>
			</script>
			            
			<script type="text/javascript">
				$(document).ready(function() {
					listenToWorkersJob();
					listenToJobsActivities();
					initPieChart();
				});
			</script>
			
			
			            
			            
			<div class="row">
				<div class="span12">
					<div class="page-header">
					<h2>Jobs</h2>
					</div>
					
					<div class="row">
					<div class="span8">
						<h3>Lastest 4 minutes activities</h3>
						<div id="lastest-jobs"></div>
					</div>
					
					<div id="job-details-modal" class="modal hide">
					    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">×</button>
                        <h3>Jobs <span class="badge badge-info"></span></h3>
                        </div>
                        
                         
                           
                        <ul class="modal-body unstyled">
                        </ul>
					</div>
	
					<div class="span4">
						
						
						<h4 class="sep">Total Stats</h4>
						
						<div class="row">
						<div class="chart-pie span1" data-success="<?php echo $stats['total']['processed'] - $stats['total']['failed']?>"
						data-failed="<?php echo $stats['total']['failed']?>"></div>
						<div class="span3">
						<dl class="dl-horizontal">
							<dt>Processed jobs</dt>
							<dd id="totalJobCount"><?php echo $stats['total']['processed']?></dd>
							<dt>Failed jobs</dt>
							<dd id="f_totalJobCount"><?php echo $stats['total']['failed']?></dd>
						</dl>
						</div>
					</div>
						
						<h4 class="sep">Active workers stats</h4>
						<div class="row">
						<div class="chart-pie span1" data-success="<?php
						    echo $stats['active']['processed'] - $stats['active']['failed']?>"
						data-failed="<?php echo $stats['active']['failed']?>"></div>
						<div class="span3">
							<dl class="dl-horizontal">
								<dt>Processed jobs</dt>
								<dd id="activeWorkersJobCount"><?php echo $stats['active']['processed']?></dd>
								<dt>Failed jobs</dt>
								<dd id="f_activeWorkersJobCount"><?php echo $stats['active']['failed']?></dd>
							</dl>
						</div>
						</div>
					</div>
					
					</div>
				</div>
				
				
				
			</div>
			<div class="row">
				<div class="span8">
				<div class="page-header">
				<h2>Workers <span class="badge badge-info"><?php echo count($workers)?></span></h2>
				</div>
				
				<?php
				if (!empty($workers)) {
				    $i=0;
				
				    foreach ($workers as $worker) {
    				    if ($i++%2==0 && $i !=0) {
    				        echo '<div class="row">';
    				    }
				
					    $workerId = str_replace('.', '', $worker['host']) . $worker['process'];
				    ?>
					<div class="span4">
						<div class="worker-list">
						<h3><?php echo $worker['host']?>:<?php echo $worker['process']; ?></h3>
						
						<div class="worker-list-inner">
						<strong><i class="icon-time"></i> Uptime : </strong>
						<time datime="<?php echo date_format($worker['start'], "r")?>" rel="tooltip" title="Started on <?php echo date_format($worker['start'], "r")?>">
						<?php echo ResqueBoard\Lib\DateHelper::ago($worker['start'])?></time>
						<br />
						<strong><i class="icon-list-alt"></i> Queues : </strong><?php array_walk($worker['queues'], function($q){echo '<span class="queue-name">'.$q.'</span> ';})?>
						
						<div class="worker-stats clearfix">
    						<div class="chart-pie span1" data-success="<?php
    						    echo $stats['active']['processed'] - $stats['active']['failed']?>"
    						data-failed="<?php echo $stats['active']['failed']?>"></div>
    						
    						
    							    <div class="span1 stat-count">
            							Processed
            							<b id="s_<?php echo $workerId; ?>" class="success"><?php echo $worker['processed']?></b>
        							</div>
        							<div class="span1 stat-count">
            							Failed
            							<b id="f_<?php echo $workerId; ?>" class="warning"><?php echo $worker['failed']?></b>
        							</div>
    							</li>
    						
    					</div>
						
						
						</div>
						</div>
					</div>
					<?php  if ($i%2==0 || $i == count($workers)) {
					    echo '</div>';
					}
					
				    }
				} ?>
				
				</div>
				
				<div class="span4">
					<div class="page-header">
					<h2>Queues <span class="badge badge-info"><?php echo count($queues)?></span></h2>
					</div>
					<?php if (!empty($queues)) {
					    echo '<table class="table table-condensed"><thead>'.
                             '<tr><th>Name</th><th>Worker count</th></tr></thead><tbody>';
					    foreach ($queues as $queue => $count) { ?>
						<tr>
							<td><?php echo $queue?></td>
							<td><?php echo $count?></td>
						</tr>
					<?php }
					echo '</tbody></table>';
				} ?>
				</div>
				
			</div>
		</div>