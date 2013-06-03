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
 * @package    ResqueBoard
 * @subpackage ResqueBoard.View
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @copyright  2012-2013 Wan Qi Chen
 * @link       http://resqueboard.kamisama.me
 * @since      1.0.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
		<footer class="container" role="contentinfo">
			<p class="pull-right">
				Current server time : <?php echo date('r'); ?>
			</p>
			<p>
				<img src="img/resqueboard.png" alt="ResqueBoard" width=16 height=16 />
				Powered by <a href="http://resqueboard.kamisama.me" title="ResqueBoard website">ResqueBoard <?php echo APPLICATION_VERSION ?></a>
			</p>
		</footer>
		<script type="text/javascript">CUBE_URL = '<?php echo CUBE_URL ?>'</script>
		<script type="text/javascript" data-main="js/main" src="js/libs/require.js"></script>
	</body>
</html>