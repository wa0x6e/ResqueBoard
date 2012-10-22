##Changelog

###v1.3.1 (2011-10-21)

* [fix] Always use the server timezone
* [fix] Searching a job by time was not working 
* [new] Add links to jobs browser from jobs load distribution matrix

###v1.3.0 (2011-10-04)

* [new] Job load distribution page
* [new] `ReadOnly` mode to disable worker stopping
* [new] `BASE_URL` constant for website url
* [change] Rename Job distribution to Job class distribution
* [change] Update code to use Slim 2

> **Upgrade notes**  
> Set the `BASE_URL` constant to your website url in Core.php  
> Update your composer dependencies with `composer.phar update` for the latest Slim 2


###v1.2.0 (2011-09-26)

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