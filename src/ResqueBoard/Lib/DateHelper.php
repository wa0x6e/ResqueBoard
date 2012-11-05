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
 * @subpackage    resqueboard.lib
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
     * @author  Umair Ashraf
     * @link    http://php.net/manual/en/datetime.diff.php
     */
    private static function pluralize($count, $text)
    {
        return $count . (($count == 1) ? (" $text") : (" ${text}s"));
    }


    /**
     *
     * @author  Umair Ashraf
     * @link    http://php.net/manual/en/datetime.diff.php
     */
    public static function ago($datetime)
    {
        $interval = date_create('now')->diff($datetime);
        $suffix = '';
        if ($v = $interval->y >= 1) {
            return self::pluralize($interval->y, 'year') . $suffix;
        }
        if ($v = $interval->m >= 1) {
            return self::pluralize($interval->m, 'month') . $suffix;
        }
        if ($v = $interval->d >= 1) {
            return self::pluralize($interval->d, 'day') . $suffix;
        }
        if ($v = $interval->h >= 1) {
            return self::pluralize($interval->h, 'hour') . $suffix;
        }
        if ($v = $interval->i >= 1) {
            return self::pluralize($interval->i, 'minute') . $suffix;
        }
        return self::pluralize($interval->s, 'second') . $suffix;
    }


    public static function getStartHour($date, $offset = null)
    {
        $d = clone $date;
        $d->setTime($d->format('H'), 0, 0);

        if ($offset !== null) {
            $d->modify($offset . ' hour');
        }

        return $d;
    }


    public static function getEndHour($date, $offset = null)
    {
        $d = clone $date;
        $d->setTime($d->format('H'), 59, 59);

        if ($offset !== null) {
            $d->modify($offset . ' hour');
        }

        return $d;
    }


    public static function getStartDay($date, $offset = null)
    {
        $d = clone $date;
        $d->setTime(0, 0, 0);

        if ($offset !== null) {
            $d->modify($offset . ' day');
        }

        return $d;
    }


    public static function getEndDay($date, $offset = null)
    {
        $d = clone $date;
        $d->setTime(23, 59, 59);

        if ($offset !== null) {
            $d->modify($offset . ' day');
        }

        return $d;
    }


    public static function getStartWeek($date, $offset = null)
    {
        $d = clone $date;
        $d->modify('monday this week')->setTime(0, 0, 0);

        if ($offset !== null) {
            $d->modify($offset . ' week');
        }

        return $d;
    }


    public static function getEndWeek($date, $offset = null)
    {
        $d = clone $date;
        $d->modify('sunday this week')->setTime(23, 59, 59);

        if ($offset !== null) {
            $d->modify($offset . ' week');
        }

        return $d;
    }


    public static function getStartMonth($date, $offset = null)
    {
        $d = clone $date;
        $d->modify('first day of this month')->setTime(0, 0, 0);

        if ($offset !== null) {
            $d->modify($offset . ' month');
        }

        return $d;
    }


    public static function getEndMonth($date, $offset = null)
    {
        $d = clone $date;
        $d->modify('last day of this month')->setTime(23, 59, 59);

        if ($offset !== null) {
            $d->modify($offset . ' month');
        }

        return $d;
    }
}
