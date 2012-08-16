<?php
/**
 * DateHelper file
 *
 * Used to format and manipulate date
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
 * @subpackage	  resqueboard.lib
 * @since         1.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\Lib;

/**
 * DateHelper Class
 *
 * Used to format and manipulate date
 *
 * @author Wan Qi Chen <kami@kamisama.me>
 */
class DateHelper
{
	/**
	 *
	 * @author	Umair Ashraf
	 * @link	http://php.net/manual/en/datetime.diff.php
	 */
    private static function pluralize( $count, $text )
    {
        return $count . ( ( $count == 1 ) ? ( " $text" ) : ( " ${text}s" ) );
    }
    
    
    /**
     *
     * @author	Umair Ashraf
     * @link	http://php.net/manual/en/datetime.diff.php
     */
    public static function ago( $datetime )
    {
        $interval = date_create('now')->diff( $datetime );
        $suffix = '';
        if ( $v = $interval->y >= 1 ) {
            return self::pluralize( $interval->y, 'year' ) . $suffix;
        }
        if ( $v = $interval->m >= 1 ) {
            return self::pluralize( $interval->m, 'month' ) . $suffix;
        }
        if ( $v = $interval->d >= 1 ) {
            return self::pluralize( $interval->d, 'day' ) . $suffix;
        }
        if ( $v = $interval->h >= 1 ) {
            return self::pluralize( $interval->h, 'hour' ) . $suffix;
        }
        if ( $v = $interval->i >= 1 ) {
            return self::pluralize( $interval->i, 'minute' ) . $suffix;
        }
        return self::pluralize( $interval->s, 'second' ) . $suffix;
    }
}