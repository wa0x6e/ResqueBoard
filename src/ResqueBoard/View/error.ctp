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
 * @author        Wan Qi Chen <kami@kamisama.me>
 * @copyright     Copyright 2012, Wan Qi Chen <kami@kamisama.me>
 * @link          http://resqueboard.kamisama.me
 * @package       resqueboard
 * @subpackage	  resqueboard.template
 * @since         1.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<div class="container">
	<div class="hero-unit alert alert-error">
		<h1>Oups ! Something went wrong</h1>
		<p><?php echo $message ?></p>

        <pre><?php print_r($trace) ?></pre>
	</div>
</div>