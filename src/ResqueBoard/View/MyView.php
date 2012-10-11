<?php
/**
 * MyView file
 *
 * Custom view class to render template
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
 * MyView Class
 *
 * Extend Slim_View to automatically include header
 * and footer when rendering a template
 *
 * @since  1.0.0
 * @author Wan Qi Chen <kami@kamisama.me>
 */
class MyView extends \Slim\View
{

    private $headerTemplate = 'header.ctp';
    private $footerTemplate = 'footer.ctp';


    /**
     * Automatically include header and footer when rendering a template
     *
     * @see Slim_View::render()
     */
    public function render($template)
    {
        $this->setTemplate($template);
        extract($this->data);

        ob_start();
        if (!isset($raw) || $raw === false) {
        	require $this->templatesDirectory . DIRECTORY_SEPARATOR . $this->headerTemplate;
        }
        require $this->templatePath;
        if (!isset($raw) || $raw === false) {
            require $this->templatesDirectory . DIRECTORY_SEPARATOR . $this->footerTemplate;
        }
        return ob_get_clean();
    }
}

