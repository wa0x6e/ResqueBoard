<?php
/**
 * Body Prepend template
 *
 * Sit between the header and the footer
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
 * @subpackage    resqueboard.template
 * @since         1.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<div id="main"<?php if (isset($ngController)) {
    echo ' ng-controller="' . $ngController . '"';
} ?>>
    <ul class="menu">
        <?php

            foreach ($navs as $link => $nav) {

                $class = array();


                if (((strpos($_SERVER['REQUEST_URI'], $nav['link']) !== false && $nav['link'] != '/' || $_SERVER['REQUEST_URI'] == '/' && $nav['link'] == '/'))) {
                    $class['root'] = array('active');
                };



                echo '<li class="dropdown">'.
                '<a href="';
                if (isset($nav['submenu'])) {
                    echo '#';
                } else {
                    echo $nav['link'];
                }
                echo '"';

                if (isset($nav['submenu'])) {
                    $class['root'][] = 'dropdown dropdown-toggle';
                }

                if (isset($class['root'])) {
                     echo ' class="'. implode(' ', $class['root']) .'"';
                }

                if (isset($nav['submenu'])) {
                    echo ' data-toggle="dropdown"';
                }

                echo '>';
                if (isset($nav['icon'])) {
                    echo '<i class="'.$nav['icon'].'"></i> ';
                }
                echo $nav['name'];

                if (isset($nav['submenu'])) {
                    echo ' <i class="icon-chevron-right caret"></i>';
                }

                echo '</a>';

                if (isset($nav['submenu'])) {
                    echo '<ul class="dropdown-menu" role="menu">';
                    foreach($nav['submenu'] as $i => $options) {
                        if ($options === '') {
                            echo '<li class="divider"></li>';
                        } else {
                            echo '<li><a href="'.$options['link'].'">';
                            echo '<i class="' . $options['icon'] . '"></i>';
                            echo $options['name'].'</a></li>';
                        }

                    }
                    echo '</ul>';
                }

                echo '</li>';
            }
        ?>
    </ul>
    <div id="body">
        <div class="page-header">
            <h1><?php echo $current['title']; ?><i class="<?php echo $current['icon']; ?>"></i></h1>
        </div>