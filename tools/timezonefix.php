<?php

/**
 * Fix timezone when using Monolog version <= 1.6.0
 *
 * If your timezone is not UTC, you need to fix all dates, as they are
 * considered UTC time, even if it's not.
 *
 * This script will update all your dates to the correct timezone
 * defined in Core.php
 *
 * ===========================
 * WARNING
 * Run this tool only once !!!
 * ===========================
 * This fix will send thousands of queries to your mongodb server
 * Run it on low traffic period
 *
 * Usage:
 * This script should be run in the shell, to avoid script timeout
 *
 * In the shell, navigate to the folder containing this file
 *     cd path/to/tool
 *
 * [optional] Dry-run the update (will not change anything)
 *     php -q timezonefix.php
 *
 * If you have a lot of jobs, a dry-run will give you an idea about how many
 * time the update will take, and the number of data to update
 *
 * Run the update
 *     php -q timezonefix.php apply
 */

namespace ResqueBoard\Lib;

set_time_limit(0);

require "../src/ResqueBoard/Config/Bootstrap.php";

use ResqueBoard\Lib\Service\Service;

$timezone = new \DateTimeZone(date_default_timezone_get());
$offset = $timezone->getOffset(new \DateTime("now", new \DateTimeZone("UTC")));
$offsetHours = round(abs($offset)/3600);
$offsetMinutes = round((abs($offset) - $offsetHours * 3600) / 60);
$offsetString = ($offset < 0 ? '-' : '+')
            . ($offsetHours < 10 ? '0' : '') . $offsetHours
            . ':'
            . ($offsetMinutes < 10 ? '0' : '') . $offsetMinutes;

if ($offsetHours == 0) {
    echo "All your dates does not need the timezone fix\n";
    die();
}

$events = array('check', 'kill', 'done', 'fail', 'fork', 'found', 'got', 'kill', 'pause',
    'process', 'prune', 'reconnect', 'resume', 'shutdown', 'signal', 'sleep', 'start');

$start = microtime(true);

foreach ($events as $eventName) {

    $collection = Service::Mongo()->selectCollection(Service::$settings['Mongo']['database'], $eventName . '_events');
    $count = $collection->count();

    echo "\nFound " . $count . " results for events : " . strtoupper($eventName) . " \n";
    echo "Start updating all " . $eventName . " dates to " . date_default_timezone_get() . " timezone\n";
    echo "--- \n";

    $cursor = $collection->find(array(), array('t' => true));

    $i = 0;
    foreach ($cursor as $data) {
        $date = new \DateTime("@" . $data['t']->sec, new \DateTimeZone("UTC"));
        $usec = "" . $data['t']->usec;
        if (count($usec) < 6) {
            $usec = str_pad($usec, 7 - count($usec), "0", STR_PAD_LEFT);
        }

        $oldDate = $date->format('Y-m-d\TH:i:s.') . $usec . "+00:00";
        $newDate = new \DateTime($date->format('Y-m-d\TH:i:s.') . $usec . $offsetString);

        echo $i . ") " . $oldDate . " -> " . $newDate->format('Y-m-d\TH:i:s.uO') . "\n";

        $i++;

        if (isset($argv[1]) && $argv[1] === "apply") {
            $d = array('t' => new \MongoDate($newDate->getTimestamp(), $usec));
            $collection->update(array('_id' => $data['_id']), array('$set' => $d));
        }
    }
}

$time = (int) (microtime(true) - $start);

echo "Executed in " . number_format($time) ." seconds\n";
