<?php
/**
 * Start Worker form template
 *
 * Website start worker form
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
 * @since      1.2.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<form class="modal-form" action="/api/workers/start" method="POST">
    <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3>Start worker</h3>
	</div>
	<div class="modal-body">

	<?php
	    if (!empty($errors)) {
            echo '<div class="alert alert-warning"><ul>';
            foreach($errors as $error) {
                echo '<li>'.$error.'</li>';
            }
            echo '</ul></div>';
        }

	?>

    <fieldset>
        <div class="span">
        <label for="f_worker-queues">Queues
        <i class="icon-info-sign pop-over" data-content="Comma separated list of queues name<br/><i>Default : 'default'</i>" data-title="<b>Queues</b>"></i></label>
        <input type="text" name="queues" value="<?php echo $data['queues'] ?>" class="span2" id="f_worker-queues" placeholder="default" />
        </div>
        <div class="span1">
        <label for="f_worker-number">Count
        <i class="icon-info-sign pop-over" data-content="Number of new workers to create<br/><i>Default : 1</i>" data-title="<b>Worker count</b>"></i></label>
        <input type="text" name="workers" class="span1" value="<?php echo $data['workers'] ?>" id="f_worker-number" placeholder="1" />
        </div>

        <div class="span1">
        <label for="f_worker-interval">Polling
        <i class="icon-info-sign pop-over" data-content="Number of second between each polling<br/><i>Default : 5</i>" data-title="<b>Polling frequency</b>"></i></label>
        <input type="text" name="interval" value="<?php echo $data['interval'] ?>" class="span1" id="f_worker-interval" placeholder="5" />
        </div>

        <div class="span2">
        <label for="f_worker-user">User
        <i class="icon-info-sign pop-over" data-content="User owning the worker process. The user must have permissions to execute your jobs<br/><i>Default : current php user</i>" data-title="<b>User</b>"></i></label>
        <input type="text" name="user" value="<?php echo $data['user'] ?>" class="span2" id="f_worker-user" placeholder="<?php echo exec('whoami'); ?>" />
        </div>

        <div class="span3">
        <label for="f_worker-log">Log filename
        <i class="icon-info-sign pop-over" data-content="Absolute path to the log file" data-title="<b>Log</b>"></i></label>
        <input type="text" name="log" value="<?php echo $data['log'] ?>" class="span3" id="f_worker-log" placeholder="/path/to/log.log" />
        </div>

        <div class="span3">
        <label for="f_worker-autoloader">Autoloader
        <i class="icon-info-sign pop-over" data-content="Absolute path to your jobs classes autoloader" data-title="<b>Autoloader</b>"></i></label>
        <input type="text" name="include" value="<?php echo $data['include'] ?>" class="span3" id="f_worker-autoloader" placeholder="/path/to/autoloader.php" />
        </div>
     </fieldset>

     <div class="accordion-group">

         <div class="accordion-heading">
         <a class="accordion-toggle" data-toggle="collapse" href="#redis-options">Redis Server</a>
        </div>

        <div id="redis-options" class="accordion-body collapse">
            <div class="accordion-inner">

                <div class="alert alert-info">Edit only if you want to use an other redis server</div>

                <div class="span2">
                <label for="f_worker-redis-host">Host</label>
                <input type="text" name="host" value="<?php echo $data['host'] ?>" class="span2" id="f_worker-redis-host" placeholder="localhost" />
                </div>

                <div class="span1">
                <label for="f_worker-redis-port">Port</label>
                <input type="text" name="port" value="<?php echo $data['port'] ?>" class="span1" id="f_worker-redis-port" placeholder="6379" />
                </div>

                <div class="span1">
                <label for="f_worker-redis-database">Database</label>
                <input type="text" name="database" value="<?php echo $data['database'] ?>" class="span1" id="f_worker-redis-database" placeholder="0" />
                </div>

                <div class="span2">
                <label for="f_worker-redis-namespace">Namespace</label>
                <input type="text" name="namespace" value="<?php echo $data['namespace'] ?>" class="span2" id="f_worker-redis-namespace" placeholder="resque" />
                </div>
            </div>
         </div>


    </div>

  </div>
   <div class="modal-footer">
       <button type="submit" class="btn" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Star worker</button>
    </div>


</form>