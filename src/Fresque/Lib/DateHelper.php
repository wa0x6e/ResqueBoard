<?php

namespace Fresque\Lib;

class DateHelper
{
    private static function pluralize( $count, $text )
    {
        return $count . ( ( $count == 1 ) ? ( " $text" ) : ( " ${text}s" ) );
    }
    
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