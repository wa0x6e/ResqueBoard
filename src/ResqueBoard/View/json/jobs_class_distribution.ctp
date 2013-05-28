<?php
/**
 * Job class distribution json template
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
 * @since         2.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

$stats = array(
	'total' => $class->total,
	'stats' => array()
);

foreach($class->stats as $datas) {
	$stats['stats'][] = array(
		'name' => $datas['_id'],
		'count' => $datas['value'],
        'perc' => $datas['value'] / $class->total * 100
	);
}

echo json_encode($stats);