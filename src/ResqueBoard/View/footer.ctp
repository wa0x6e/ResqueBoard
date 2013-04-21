<?php
/**
 * Footer template
 *
 * Included after each template
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
		<footer class="container">
			<p class="pull-right">
				Current server time : <?php echo date('r'); ?>
			</p>
			<p>
				<img src="/img/resqueboard.png" alt="ResqueBoard" width=16 height=16 />
				Powered by <a href="http://resqueboard.kamisama.me" title="ResqueBoard website">ResqueBoard <?php echo APPLICATION_VERSION ?></a>
			</p>
		</footer>

		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
		<script type="text/javascript" src="/js/jquery.jsrender-1.0.min.js"></script>
		<script type="text/javascript" src="/js/moment-1.7.2.min.js"></script>
		<script type="text/javascript" src="/js/jquery.cookie-1.1.min.js"></script>
		<script type="text/javascript" src="/js/d3.v3.0.4.min.js"></script>
		<script type="text/javascript" src="/js/cubism.v1.2.2.min.js"></script>
		<script type="text/javascript" src="/js/bootstrap-2.1.0.min.js"></script>
		<script type="text/javascript" src="/js/infinite-scroll-2.0.min.js"></script>
		<script src="//yandex.st/highlightjs/7.3/highlight.min.js"></script>
		<script type="text/javascript" src="/js/cal-heatmap.min.js"></script>
		<script type="text/javascript" src="/js/app.js?v=<?php echo APPLICATION_VERSION ?>"></script>
	</body>
</html>