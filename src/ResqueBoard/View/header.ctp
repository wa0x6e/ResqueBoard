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
		<base href="//<?php echo $_SERVER['HTTP_HOST'] ?>" />
		<title><?php echo $pageTitle . TITLE_SEP . APPLICATION_NAME?></title>
		<link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css">
		<link href="/css/main.css?v=<?php echo APPLICATION_VERSION ?>" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="http://yandex.st/highlightjs/7.3/styles/default.min.css">
		<link href='//fonts.googleapis.com/css?family=Fjalla+One|PT+Sans:400,700' rel='stylesheet' type='text/css'>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	</head>
	<body>
		<script type="text/javascript">CUBE_URL = '<?php echo CUBE_URL ?>'</script>

		<div class="container">
			<a class="brand" href="/"><em><?php echo APPLICATION_NAME ?></em>Analytics for Resque PHP</a>
		</div>