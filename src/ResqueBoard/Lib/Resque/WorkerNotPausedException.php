<?php
/**
 * WorkerNotPausedException class
 *
 * Type of exception thrown when tryin to pause an already paused worker
 *
 * PHP version 5
 *
 * @package    ResqueBoard
 * @subpackage ResqueBoard.Lib.Resque
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @copyright  2012-2013 Wan Qi Chen
 * @link       http://resqueboard.kamisama.me
 * @since      2.0.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\Lib\Resque;

class WorkerNotPausedException extends \Exception
{
}
