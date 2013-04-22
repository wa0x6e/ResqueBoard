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
<div id="main">
    <ul class="menu">
        <?php
            $navs = array(
                        '/' => array(
                            'icon' => 'icon-home',
                            'title' => 'Home'
                            ),
                        '/workers' => array(
                            'icon' => 'icon-cogs',
                            'title' => 'Workers'),
                        '/logs' => array(
                            'icon' => 'icon-file',
                            'title' => 'Logs',
                            'submenu' => array(
                                '<i class="icon-eye-open"></i> Latest activities' => '/logs',
                                '<i class="icon-eye-open"></i> Browse archives' => '/logs/browse',
                            )
                        ),
                        '/jobs' => array(
                                'icon' => 'icon-briefcase',
                                'title' => 'Jobs',
                                'submenu' => array(
                                    '<i class="icon-dashboard"></i> <strong>Jobs Dashboard</strong>' => '/jobs',
                                    '',
                                    '<i class="icon-eye-open"></i> Processed jobs' => '/jobs/view',
                                    '<i class="icon-eye-open"></i> Pending jobs' => '/jobs/pending',
                                    '<i class="icon-bar-chart"></i> Scheduled Jobs' => '/jobs/scheduled',
                                    '',
                                    '<i class="icon-tasks"></i> Class distribution' => 'jobs/distribution/class',
                                    '<i class="icon-table"></i> Load distribution' => '/jobs/distribution/load',
                                    '<i class="icon-bar-chart"></i> Load overview' => '/jobs/overview/hour'

                                )
                         )
                    );

            foreach ($navs as $link => $nav) {

                $class = array();


                if (((strpos($_SERVER['REQUEST_URI'], $link) !== false && $link != '/' || $_SERVER['REQUEST_URI'] == '/' && $link == '/'))) {
                    $class['root'] = array('active');
                };



                echo '<li class="dropdown">'.
                '<a href="';
                if (isset($nav['submenu'])) {
                    echo '#';
                } else {
                    echo $link;
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
                echo $nav['title'];

                if (isset($nav['submenu'])) {
                    echo ' <i class="icon-chevron-right caret"></i>';
                }

                echo '</a>';

                if (isset($nav['submenu'])) {
                    echo '<ul class="dropdown-menu" role="menu">';
                    foreach($nav['submenu'] as $title => $link) {
                        if ($link === '') {
                            echo '<li class="divider"></li>';
                        } else {
                            echo '<li><a href="'.$link.'">'.$title.'</a></li>';
                        }

                    }
                    echo '</ul>';
                }

                echo '</li>';
            }
        ?>
    </ul>
    <div id="body">