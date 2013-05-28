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
 * @author        Wan Qi Chen <kami@kamisama.me>
 * @copyright     Copyright 2012, Wan Qi Chen <kami@kamisama.me>
 * @link          http://resqueboard.kamisama.me
 * @package       resqueboard
 * @subpackage	  resqueboard.lib
 * @since         1.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
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
