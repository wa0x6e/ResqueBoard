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
		<title><?php echo $pageTitle . TITLE_SEP . APPLICATION_NAME?></title>
		<link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css">
		<link href="/css/main-<?php echo APPLICATION_VERSION ?>.css" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
		<link rel="stylesheet" href="http://yandex.st/highlightjs/7.1/styles/default.min.css">
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
											'/' => 'Home',
											'/logs' => 'Logs',
											'/workers' => 'Workers',
											'/jobs' => 'Jobs'
										);

								foreach ($navs as $link => $nav) {
									echo '<li'. ((strpos($_SERVER['REQUEST_URI'], $link) !== false && $link != '/' || $_SERVER['REQUEST_URI'] == '/' && $link == '/') ? ' class="active"' : '').'>'.
									'<a href="'.$link.'">'.$nav.'</a></li>';
								}
							?>
						</ul>
				</div>
			</div>
		</div>