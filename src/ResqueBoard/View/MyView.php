<?php

namespace ResqueBoard\View;

class MyView extends \Slim_View
{
    
    private $headerTemplate = 'header.php';
    private $footerTemplate = 'footer.php';
    
    public function render( $template )
    {
       $this->setTemplate($template);
       extract($this->data);
       ob_start();
       require $this->templatesDirectory . DIRECTORY_SEPARATOR . $this->headerTemplate;
       require $this->templatePath;
       require $this->templatesDirectory . DIRECTORY_SEPARATOR . $this->footerTemplate;
       return ob_get_clean();
       return $content;
    }
}