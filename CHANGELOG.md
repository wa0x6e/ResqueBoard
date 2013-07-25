##Changelog

###v2.0.0-beta-4 (2013-07-25)

* Update code Slim 2.3

###v2.0.0-beta-3 (2013-05-29)

* ResqueBoard now works if installed in a subfolder
* Cache all non-realtime datas
* Load javascript with RequireJS
* Split javascript code into multiples modules/files
* Grouping all databases related functions inside a `Service` module
* Refactoring worker handling API
* Use only local assets instead of a CDN
* Various UI fixes and update

> **Upgrade notes**  
> PHP 5.4 is required for this beta  
> Just for reference, at least IE9 is required  
> run `composer update` to install/update new dependencies



###v1.5.0 (2013-02-21)

* [new] Scheduled jobs tab :
  * View global stats about scheduled jobs (total, expired, future jobs)
  * Calendar heat map to visualize future scheduled jobs
  * View scheduled jobs details by date
* [new] Scheduled jobs stats in home page
* [new] Add scheduled jobs in Job Overview graph
* [new] Pending jobs page
* [new] Warn when a queue is not monitored by a worker

###v1.4.0 (2013-01-23)

* [fix] Manipulation of number greater than 1 million return NaN
* [new] Display processing time in job details
* [new] Display Waiting Jobs in Jobs Dashboard
* [new] Jobs Overview page : graph to compare number of processed/failed jobs, and processing time over various time interval
* [change] Waiting Jobs counter in Job Dashboard now takes into account jobs from inactive queues
* [change] All time set to server timezone
* [other] Update JS libraries

> **Upgrade notes**  
> Job processing time requires php-resque-ex v1.0.15  
> Only newer jobs processed with this version of php-resque-ex will have a processing time


###v1.3.1 (2012-10-21)

* [fix] Always use the server timezone
* [fix] Searching a job by time was not working
* [new] Add links to jobs browser from jobs load distribution matrix

###v1.3.0 (2012-10-04)

* [new] Job load distribution page
* [new] `ReadOnly` mode to disable worker stopping
* [new] `BASE_URL` constant for website url
* [change] Rename Job distribution to Job class distribution
* [change] Update code to use Slim 2

> **Upgrade notes**
> Set the `BASE_URL` constant to your website url in Core.php
> Update your composer dependencies with `composer.phar update` for the latest Slim 2


###v1.2.0 (2012-09-26)

* [fix] Fix getting jobs by date where date was already a timestamp
* [fix] Validate JS with JSLint
* [new] Listen `start` and `stop` event in logs
* [new] Workers and queues lists on Index tab are dynamic, and respond to start and stop events
* [new] Workers List on Worker tab are dynamic, and respond to start/stop events
* [new] Stop a worker
* [new] Search and browse logs
* [new] Add links to job details in logs
* [new] Add infinite Scroll to logs and jobs browser
* [ui] Some new shiny icons, and UI fixes

###v1.1.0 (2012-09-04)

* [change] PHP Code PSR2 valid
* [change] HTML Code HTML5 valid
* [change] Cookie usage retrained to /logs
* [change] Move jobs viewer out of jobs dashboard
* [change] Update d3.js, Cubism and moment.js libraries
* [change] Update to boostrap 2.1.0
* [new] View jobs class distribution (job tab)
* [new] More jobs stats (job tab)
* [new] Add past hour jobs load chart (job tab)
* [new] Add Monthly jobs load chart (job tab)
* [new] Advanced job search
* [new] Add database option in config to connect to specified redis database

###v1.0.0 (2012-08-16)

* Birth