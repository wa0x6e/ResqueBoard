<?php
/**
 * JsonView file
 *
 * Custom view class to render json data
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
 * @since      2.0.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\View;

/**
 * JsonView Class
 *
 * @since  2.0.0
 * @author Wan Qi Chen <kami@kamisama.me>
 */
class JsonView extends \Slim\View
{
    /**
     * Automatically include header and footer when rendering a template
     *
     * @see Slim_View::render()
     */
    public function render($template)
    {
        return parent::render('json' . DS . $template);
    }
}
