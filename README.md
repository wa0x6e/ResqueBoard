#RescueBoard

RescueBoard is a web interface to monitor your php-resque activities.

It's different from the other web interface, like the one shipped with the [original resque](https://github.com/defunkt/resque/#the-front-end), that display only what's happening right now, and then forget it.  
RescueBoard log **absolutely everything** your workers are doing, and can compute various stats about your workers health, in addition to pushing live datas about your workers status.

##Goals
RescueBoard is mainly built for 2 objectives :

* seeing what's happening right now in realtime
* visualize what's happened in the past with various charts, to easily benchmarks your various workers

It can :

* Display a real-time log of your php-resque activities
* Display stats about the health of each workers
* Display in real-time what your workers are doing

###Todo
* Display job informations
* More charts

##Demo

A [demo](http://resque.neetcafe.com/) is worth a thousands words. Demo is monitoring a beta site, so there's no much activity to log.

##Install

###Tools needed
* My forked version of php-resque
* Cube by Square (require MongoDB and Node.js)

####Why a forked version of php-resque ?
The granularity of the logs are far beyond what resque can provide us by just reading the resque database.  
You can't use the official php-resque library, as :

* it does not provide enough datas for each log
* it can not log outside of SDTOUT

The forked php-resque will log all the events to Cube.

###Installation

####ResqueBoard
Clone this repository
	
	git clone git://github.com/kamisama/ResqueBoard.git 
	
Install Composer and download all dependencies

	curl -s https://getcomposer.org/installer | php
	php composer.phar install
	
Point Apache to	ResqueBoard/src/ResqueBoard/webroot

####Php-Resque

Clone the forked php-resque

	git clone git://
	
and replace your current php-resque library with this one. This fork should not break anything, as only the logging method are changed.

####Cube

Refer to Cube official documentation. You will have to install MongoDB and Node.js if you haven't yet.
Then start Cube.

##Configuration

Your ResqueBoard should be running out-of-the-box. If you have a different hostname or port for your mongodb or your redis database, edit the `ResqueBoard/src/ResqueBoard/Config/Core.php` file.

Voila !

##Credits

* Original php-resque by chrisboulton
* Monolog, for handling all the log events in php-resque
* Cube for storing and serving all log events in realtime
* d3.js for drawing all the charts
* Twitter bootstrap for template
* Slim, for powering the application
* Composer, for managing all the dependencies
* Fugue, for the favicon
* Jquery