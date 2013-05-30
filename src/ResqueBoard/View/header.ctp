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
		<base href="<?php echo URL_ROOT ?>" />
		<title><?php echo $pageTitle . TITLE_SEP . APPLICATION_NAME?></title>
		<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
		<link href="css/main.css?v=<?php echo APPLICATION_VERSION ?>" rel="stylesheet" type="text/css">
		<link href="css/highlightjs/zenburn.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div class="container">
			<?php

				$serviceStatus = array(
					'Redis' => false,
					'Mongo' => false,
					'Cube' => false
				);

				try {
					\ResqueBoard\Lib\Service\Service::Redis();
					$serviceStatus['Redis'] = true;
				} catch (\Exception $e) {
					$serviceStatus['Redis'] = $e->getMessage();
				}

				try {
					\ResqueBoard\Lib\Service\Service::Mongo();
					$serviceStatus['Mongo'] = true;
				} catch (\Exception $e) {
					$serviceStatus['Mongo'] = $e->getMessage();
				}
			?>
			<ul id="server-status" class="unstyled">
				<li class="title">server status</li>
				<?php foreach ($serviceStatus as $name => $status) {
					echo '<li data-server="'. strtolower($name) . '" data-placement="bottom" data-trigger="hover" data-event="popover" title="' . $name . ' server" class="status ';
					if ($status === true) {
						echo 'status-ok" data-content="The ' . $name . ' server is online">';
					} elseif ($status !== false) {
						echo 'status-error" data-content="Unable to connect to the ' . $name . ' server">';
					} else {
						echo 'status-unknown" data-content="<i class=\'icon-spinner icon-spin\'></i> Fetching the ' . $name . ' server status">';
					}
					echo '</li>';
				} ?>
			</ul>
			<a class="brand" href="<?php echo URL_ROOT ?>"><em><?php echo APPLICATION_NAME ?></em>Analytics for Resque PHP</a>
		</div>