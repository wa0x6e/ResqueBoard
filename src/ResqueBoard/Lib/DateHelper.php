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
 * @package    ResqueBoard
 * @subpackage ResqueBoard.Lib
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @copyright  2012-2013 Wan Qi Chen
 * @link       http://resqueboard.kamisama.me
 * @since      1.0.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\Lib;

/**
 * DateHelper Class
 *
 * Used to format and manipulate date
 *
 * @subpackage ResqueBoard.Lib
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @since      1.0.0
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
        if ($interval->y >= 1) {
            return self::pluralize($interval->y, 'year') . $suffix;
        }
        if ($interval->m >= 1) {
            return self::pluralize($interval->m, 'month') . $suffix;
        }
        if ($interval->d >= 1) {
            return self::pluralize($interval->d, 'day') . $suffix;
        }
        if ($interval->h >= 1) {
            return self::pluralize($interval->h, 'hour') . $suffix;
        }
        if ($interval->i >= 1) {
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


    /**
     * Convert convert a number of millisecond to a humanly understandable english date
     * @param  int      $ms Milliseconds
     * @return string
     */
    public static function humanize($ms)
    {
        $time = self::explodeMilliseconds($ms);

        // Display milliseconds only if total ms is less than 1 min
        // We don't need milliseconds precision with big values


        // Again, if more or equal than one day, don't need seconds precision
        if (!empty($time['day'])) {

            if (empty($time['hour']) && empty($time['min'])) {
                return $time['day'] . ' days';
            }

            if (!empty($time['hour']) && empty($time['min'])) {
                return $time['day'] . ' days ' . $time['hour'] . ' hours';
            }

            if (empty($time['hour']) && !empty($time['min'])) {
                return $time['day'] . ' days ' . $time['min'] . ' min';
            }

            return $time['day'] . ' days ' . $time['hour'] . 'h' . str_pad($time['min'], 2, '0', STR_PAD_LEFT);
        }

        if (!empty($time['hour'])) {
            return $time['hour'] . ':' . str_pad($time['min'], 2, '0', STR_PAD_LEFT) . ' hours';
        }

        if (!empty($time['min'])) {
            return $time['min'] . ':' . str_pad($time['sec'], 2, '0', STR_PAD_LEFT) . ' min';
        }

        return $time['sec'] . ':' . str_pad($time['ms'], 3, '0', STR_PAD_LEFT) . ' sec';
    }


    public static function explodeMilliseconds($ms)
    {
        $time = array(
                'day' => 0,
                'hour' => 0,
                'min' => 0,
                'sec' => 0,
                'ms' => 0
            );

        $time['sec'] = (int)($ms/1000);
        $time['ms'] = (int) (round($ms/1000 - $time['sec'], 3)*1000);

        if ($time['sec'] > 60) {
            $time['min'] = (int)floor($time['sec'] / 60);
            $time['sec'] = (int)fmod($time['sec'], 60);
        }

        if ($time['min'] > 60) {
            $time['hour'] = (int)floor($time['min'] / 60);
            $time['min'] = (int)fmod($time['min'], 60);
        }

        if ($time['hour'] > 24) {
            $time['day'] = (int)floor($time['hour'] / 24);
            $time['hour'] = (int)fmod($time['hour'], 24);
        }

        return $time;
    }
}
