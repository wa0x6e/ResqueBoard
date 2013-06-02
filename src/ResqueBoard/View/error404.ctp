<?php
/**
 * Error template
 *
 * Website error page
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
 * @since      2.0.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<div class="container">
	<div class="hero-unit" id="page-error">
		<h1><?php echo $pageTitle ?></h1>
		<p><?php echo $message ?></p>
        <a href="<?php echo URL_ROOT ?>" class="btn btn-primary btn-large">Go back to home page</a>
	</div>
</div>