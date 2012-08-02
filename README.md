#ResqueBoard (beta)

ResqueBoard is a web interface to monitor your php-resque activities.

It's different from the other web interface, like the one shipped with the [original resque](https://github.com/defunkt/resque/#the-front-end), that display only what's happening right now, and then forget it.  
ResqueBoard log **absolutely everything** your workers are doing, and can compute various stats about your workers health, in addition to pushing live datas about your workers status.

##Goals
ResqueBoard is mainly built for 2 objectives :

* seeing what's happening right now in realtime
* visualize what's happened in the past with various charts, to easily benchmarks your various workers

It can :

* Display a realtime log of your php-resque activities
* Display stats about the health of each workers
* Display in realtime what your workers are doing

###Todo
* Display job informations
* More charts

##Demo

A [demo](http://resque.neetcafe.com/) is worth thousands words. Demo is monitoring a beta site, so there's no much activity to log.

##Install

###Tools needed
* My [forked version of php-resque](https://github.com/kamisama/php-resque) (require Redis)
* [Cube](https://github.com/square/cube) (require MongoDB and Node.js)
* A browser with websocket support

####Why a forked version of php-resque ?
The granularity of the logs are far beyond what resque can provide us by just reading the redis database.  
You can't use the official php-resque library, as :

* it does not provide enough datas for each log
* it can not log outside of SDTOUT

The forked php-resque will use UDP to log all the events to mongodb, via Cube.

###Installation

####ResqueBoard
Clone this repository
	
	$ git clone git://github.com/kamisama/ResqueBoard.git 
	
Install Composer and download all dependencies

	$ curl -s https://getcomposer.org/installer | php
	$ php composer.phar install
	
Point Apache to	`ResqueBoard/src/ResqueBoard/webroot`. I recommend creating a new subdomain, like *resque.mydomain.com*, and password protect it.

####Php-Resque

Clone the forked php-resque

	$ git clone git://github.com/kamisama/php-resque.git
	
and replace your current php-resque library with this one. This fork should not break anything, as only the logging method are changed.

####Cube

Refer to [Cube official documentation](https://github.com/square/cube/wiki). You will have to install MongoDB and Node.js if you haven't already.
Then start Cube.

##Configuration

Your ResqueBoard should be running out-of-the-box. If your mongodb or redis database are running under different hostname or port than the default one, edit the `ResqueBoard/src/ResqueBoard/Config/Core.php` file.

Voila !

##Credits

* [Original php-resque by chrisboulton](https://github.com/chrisboulton/php-resque)
* [Monolog](https://github.com/Seldaek/monolog), for handling all the log events in php-resque
* [Cube](https://github.com/square/cube) for storing and serving all log events in realtime
* [d3.js](http://d3js.org/) for drawing all the charts
* [Twitter bootstrap](http://twitter.github.com/bootstrap/) for template
* [Slim](http://www.slimframework.com), for powering the application
* [Fugue](http://p.yusukekamiyamane.com/), for the favicon