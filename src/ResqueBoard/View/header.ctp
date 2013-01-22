<?php
/**
 * Header template
 *
 * Included before each template
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
 * @subpackage	  resqueboard.template
 * @since         1.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<base href="<?php echo BASE_URL ?>" />
		<title><?php echo $pageTitle . TITLE_SEP . APPLICATION_NAME?></title>
		<link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css">
		<link href="/css/main-<?php echo APPLICATION_VERSION ?>.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="http://yandex.st/highlightjs/7.3/styles/default.min.css">
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	</head>
	<body>
		<script type="text/javascript">serverIp = '<?php echo $_SERVER['SERVER_ADDR'] ?>'</script>
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<a class="brand" href="/"><img src="/img/resqueboard_24.png" width=24 height=24 alt="<?php echo APPLICATION_NAME ?>" title="<?php echo APPLICATION_NAME ?>" /> <?php echo APPLICATION_NAME ?></a>
						<ul class="nav">
							<?php
								$navs = array(
											'/' => array('title' => 'Home'),
											'/logs' => array('title' => 'Logs'),
											'/workers' => array('title' => 'Workers'),
											'/jobs' => array(
                                                    'title' => 'Jobs',
                                                    'submenu' => array(
                                                        '<i class="icon-dashboard"></i> Dashboard' => '/jobs',
                                                    	false,
                                                    	'<i class="icon-eye-open"></i> Jobs browser' => '/jobs/view',
                                                        '<i class="icon-tasks"></i> Jobs class distribution' => 'jobs/distribution/class',
                                                        '<i class="icon-table"></i> Jobs load distribution' => '/jobs/distribution/load',
                                                        '<i class="icon-bar-chart"></i> Jobs load overview' => '/jobs/overview/hour'
                                                    )
                                             )
										);

								foreach ($navs as $link => $nav) {

                                    $class = array();
                                    if (((strpos($_SERVER['REQUEST_URI'], $link) !== false && $link != '/' || $_SERVER['REQUEST_URI'] == '/' && $link == '/'))) {
                                        $class[] = 'active';
                        			};

                        			if (isset($nav['submenu'])) {
                                        $class[] = 'dropdown';
                                    }

									echo '<li class="'. implode(' ', $class) .'">'.
									'<a href="'.$link.'"';

									if (isset($nav['submenu'])) {
                                        echo 'class="dropdown-toggle" data-toggle="dropdown" id="l-'.strtolower($nav['title']).'" data-target="#"';
                                    }

									echo '>'.$nav['title'];
									if (isset($nav['submenu'])) {
                                        echo ' <b class="caret"></b>';
                                    }
									echo '</a>';

									if (isset($nav['submenu'])) {
                                        echo '<ul class="dropdown-menu" role="menu" aria-labelledby="l-'.strtolower($nav['title']).'">';
                                        foreach($nav['submenu'] as $title => $link) {

                                            if ($link === false) {
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
				</div>
			</div>
		</div>