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
 * @package    ResqueBoard
 * @subpackage ResqueBoard.View
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @copyright  2012-2013 Wan Qi Chen
 * @link       http://resqueboard.kamisama.me
 * @since      1.0.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
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

    private $bodyPrepend = 'body_prepend.ctp';
    private $bodyAppend = 'body_append.ctp';


    /**
     * Automatically include header and footer when rendering a template
     *
     * @see Slim_View::render()
     */
    public function render($template)
    {
        $templatePathname = $this->getTemplatePathname($template);
        extract($this->data->all());

        ob_start();
        if (!isset($raw) || $raw === false) {
            require $this->templatesDirectory . DIRECTORY_SEPARATOR . $this->headerTemplate;

            if (strpos($template, 'error') === false) {
                require $this->templatesDirectory . DIRECTORY_SEPARATOR . $this->bodyPrepend;
            }
        }
        require $templatePathname;
        if (!isset($raw) || $raw === false) {
            if (DEBUG) {
                require $this->templatesDirectory . DIRECTORY_SEPARATOR . 'service_helper.ctp';
            }

            if (strpos($template, 'error') === false) {
                require $this->templatesDirectory . DIRECTORY_SEPARATOR . $this->bodyAppend;
            }

            require $this->templatesDirectory . DIRECTORY_SEPARATOR . $this->footerTemplate;
        }
        return ob_get_clean();
    }
}
