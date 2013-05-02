<?php
/**
 * WorkerNotPausedException class
 *
 * Type of exception thrown when tryin to pause an already paused worker
 *
 * @subpackage      resqueboard.lib.resque
 * @since           2.0.0
 * @author          Wan Qi Chen <kami@kamisama.me>
 */

namespace ResqueBoard\Lib\Resque;

class WorkerNotPausedException extends \Exception
{
}
