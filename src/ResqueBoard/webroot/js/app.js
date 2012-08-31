/**
 * ResqueBoard Javascript File
 *
 * Make all the numbers on the screen blink
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author		Wan Qi Chen <kami@kamisama.me>
 * @copyright     Copyright 2012, Wan Qi Chen <kami@kamisama.me>
 * @link			http://resqueboard.kamisama.me
 * @since		 1.0.0
 * @license	 MIT License (http://www.opensource.org/licenses/mit-license.ctp)
 */

var stop = new Date(Date.now());

/**
 * Convert a date to ISO 8601 format
 * @param  Date date A date object
 * @return string An ISO 8601 formatted date
 */
var formatISO = function(date){
	var format = d3.time.format.iso;
	return format.parse(date); // returns a Date
}

/**
 * Duration of the transition animation
 * @type int
 */
var duration = 1500;

/**
 * Datas about the polling delay
 * @type Object
 */
var step = {second : 10, code : "1e4"};


/**
 * Display lastest jobs stats
 * 
 * @return void
 */
function listenToJobsActivities()
{
	/**
	 * Number of division to display
	 * @type int
	 */
	var limit = 25;

	d3.json('http://'+serverIp+':1081/1.0/metric/get'+
		'?expression=sum(got)'+
		'&start=2012-07-07T16:00:00Z'+
		'&stop=' + formatISO(stop)+
		'&limit=' + limit + 
		'&step=' + step.code, function(data){

			var 
				margin = {top:25, right:35, bottom: 35, left: 20},
				width = 620 - margin.right - margin.left,
				height = 180 - margin.top - margin.bottom,
				barHeight = height,
				barWidth = width/limit - 2,
				barGutter = 0,
				barLabelHeight = 20
			;

			/**
			 * Find the next time after the last entry
			 * because the last entry doesn't have a x axis
			 */
			var getNextTick = function(date) {
				date = new Date(date);
				return new Date(date.setSeconds(date.getSeconds() + step.second));
			}

			data = data.map(function(d){return {time:new Date(d.time), value:  d.value};}).slice(0, limit);

			var x = d3.time.scale()
			.domain([data[0].time, getNextTick(data[data.length-1].time)])
			.range([0,width])
			;

			var y = d3.scale.linear()
			.domain([0,d3.max(data.map(function(d){return d.value;}))])
			.rangeRound([height,0]);


			var chart = d3.select("#lastest-jobs").append("svg")
			.attr("class", "chart")
			.attr("width", width + margin.left + margin.right)
			.attr("height", height + margin.top + margin.bottom)
			.append("g")
			.attr("transform", "translate(" + margin.left  + "," + margin.top + ")")
			;

			chart.append("svg:clipPath")
			.attr("id", "clip")
			.append("svg:rect")
			.attr("width", width)
			.attr("height", height)
			;


			chart.append("svg:text")
			.attr("x", width)
			.attr("y", -5)
			.attr("text-anchor", "end")
			.text("jobs/10sec")
			.attr("class", "axis-legend")
			;

			chart.append("svg:text")
			.attr("x", width / 2)
			.attr("y", height + barLabelHeight + 10)
			.attr("text-anchor", "middle")
			.text("time (min:sec)")
			.attr("class", "axis-legend")
			;

			var barArea = chart
			.append("svg")
			.attr("clip-path", "url(#clip)")
			.attr("class", "bar-area")
			;

			var barProp = function(selection)
			{
				selection
				.attr("title", function(d){return d.value;})
				.attr("data-target", "#job-details-modal")
				.on("click", function(d){
					displayJobsModal(d.time);
				})
				.call(barDim)
				;
			}

			var barDim = function(selection)
			{
				selection
				.attr("x", function(d) { return x(d.time) + barGutter; })
				.attr("y", function(d) { return y(d.value); })
				.attr("width", barWidth)
				.attr("height", function(d) { return height - y(d.value);  })
				;
			}


			barArea.selectAll("rect")
			.data(data)
			.enter()
			.append("rect")
			.call(barProp)
			;

			var barLabelProp = function(selection)
			{
				selection
				.attr("text-anchor", "middle")
				.attr("class", function(d){if ((height-y(d.value))<=barLabelHeight){ return "bar-label out";} return "bar-label";})
				.text(function(d){return d.value;})
				.call(barLabelDim)
				;
			}

			var barLabelDim = function(selection)
			{
				selection
				.attr("y", function(d) { return ((height-y(d.value))>barLabelHeight ? y(d.value) : (y(d.value) - barLabelHeight)); })
				.attr("dy", "1.5em")
				.attr("x", function(d) { return x(d.time) + barWidth/2; })
				;
			}

			barArea.selectAll("text").data(data).enter().append("text")
			.attr("x", function(d) { return x(d.time) + barWidth/2; })
			.call(barLabelProp)
			;

			var xAxis = d3.svg.axis()
			.scale(x)
			.ticks(d3.time.seconds, 30)
			.tickFormat(d3.time.format("%M:%S"))
			.tickSubdivide(2)
			.tickSize(6,3,0)
			.orient("bottom");

			var yAxis = d3.svg.axis()
			.scale(y)
			.tickSize(6,3,0)
			.orient("right")
			.ticks(4);

			var xAxisParent = chart.append("g")
			.attr("transform", "translate(0," + height + ")")
			.attr("class", "x-axis")
			.call(xAxis);

			var yAxisParent = chart.append("g")
			.attr("class", "y-axis")
			.attr("transform", "translate(" + width + ",0)")
			.call(yAxis);


			function redraw() {

				x.domain([data[0].time, getNextTick(data[data.length-1].time)]);
				y.domain([0,d3.max(data.map(function(d){return d.value;}))])

				var rect = barArea.selectAll("rect").data(data, function(d){return d.time;});
				var text = barArea.selectAll("text").data(data, function(d){return d.time;});

				// BAR
				// *****
				rect.enter().insert("rect")
				.call(barProp)
				.attr("x", function(d, i) { return x(getNextTick(d.time)); })
				.attr("clip-path", "url(#clip)")
				.transition()
				.duration(duration)
				.attr("x", function(d) { return x(d.time) + barWidth + 2; })
				;

				
				rect.transition()
				.duration(duration)
				.attr("clip-path", "url(#clip)")
				.call(barDim)
				;

				rect.exit().transition()
				.duration(duration)
				.attr("x", function(d, i) { return x(d.time) + barGutter; })
				.remove()
				;


				// TEXT
				// *****
				text.enter().insert("text")
				.call(barLabelProp)
				.attr("x", function(d) { return x(getNextTick(getNextTick(d.time))) - barWidth/2; })
				.transition()
				.duration(duration)
				.attr("x", function(d) { return x(d.time) + barWidth/2; })
				;


				text.transition()
				.duration(duration)
				.call(barLabelDim)
				;


				text.exit().transition()
				.duration(duration)
				.attr("x", function(d) { return x(d.time) + barWidth/2; })
				.remove()
				;


				// AXIS
				// *****
				xAxisParent.transition()
				.duration(duration)
				.call(xAxis);

				yAxisParent.transition()
				.duration(duration)
				.call(yAxis);

			}

			var metricSocket = new WebSocket("ws://"+serverIp+":1081/1.0/metric/get");

			metricSocket.onopen = function() {
				var nextDate = getNextTick(data[data.length-1].time);

				metricSocket.send(JSON.stringify({
					"expression": "sum(got)",
					"start" : nextDate.toISOString(),
					"stop": getNextTick(nextDate).toISOString(),
					"limit": 1,
					"step" : step.code
				}));
			};

			metricSocket.onmessage = function(message) {
				JsonData = JSON.parse(message.data);

				if(JsonData.hasOwnProperty("value")) {
					JsonData.time = new Date(JsonData.time);
					data.shift();
					data.push(JsonData);
					redraw();

					setTimeout(function() { 
						var nextDate = getNextTick(data[data.length-1].time);
						metricSocket.send(JSON.stringify({
							"expression": "sum(got)",
							"start" : nextDate.toISOString(),
							"stop": getNextTick(nextDate).toISOString(),
							"limit": 1,
							"step" : step.code 
						}))
					}, step.second * 1000);
				}
			};
		}
	);
}



/**
 * Display a modal with jobs details for all
 * jobs between a `start` and an `end` date.
 * End time is automatically computed from the
 * start time, and step.second
 * 
 * @param  string startTime start time in ISO 8601 format
 * @return void
 */
function displayJobsModal(startTime)
{
	startTimeStamp = (Date.parse(startTime))/1000;

	var modalTimestamp = $("#job-details").data("timestamp");
	if (modalTimestamp != startTimeStamp)
	{
		$.ajax({
			url : "/api/jobs/" + startTimeStamp + "/" + (startTimeStamp + step.second),
			success : function(message){
					$("#job-details .modal-body").html(
						$("#jobs-tpl").render(message)
					);
					$("#job-details").data('timestamp', startTimeStamp);
					$("#job-details .modal-header .badge").html(message.length);

					$("#job-details").modal('show');
				}
			
		});
	}
	else
		$("#job-details-modal").modal('show'); // Repeat because ajax is asynchronous
}



/**
 * Display new events in realtime
 * 
 * @return void
 */
function loadLogs()
{
	var counters = {
		general : {g: $("[data-rel=log-counter]")},
		type : {},
		verbosity : {}
	};

	var addCounter = function(cat, type, dom) {
			counters[cat][type] = dom;
	};

	var incrCounter = function(cat, type, step) {
			var node = counters[cat][type];
			node.html(parseInteger(node.html()) + step);
			if (node.queue("fx") == 0)
				node.effect("highlight");
	};

	var decrCounter = function(cat, type, step) {
			var node = counters[cat][type];
			var count = parseInteger(node.html());
			if (count - step >= 0) 
			{
				node.html(count - step);
				if (node.queue("fx") == 0)
					node.effect("highlight");
			}
	};

	// Update counters, by passing a list of nodes to be removed
	var updateCounters = function(nodeList){
	 
		nodeList.each(function(){

			var v = $(this);

			decrCounter("type", v.data("type"), 1);
			decrCounter("verbosity", v.data("verbosity"), 1);
			decrCounter("general", "g", 1);
			
		});
	};

	var resetCounters = function() {
		for(cat in counters)
		{
			for (type in counters[cat])
			{
				counters[cat][type].html(0);
			}
		}
	};

	var events = {
		sleep   : {expression: "sleep", format: function(data){return "for " + data.second + " seconds";}},
		got     : {expression: "got", format: function(data){return "job #" + data.args.payload.id;}},
		process : {expression: "process", format: function(data){return "job #" + data.job_id;}},
		fork    : {expression: "fork", format: function(data){return "job #" + data.job_id;}},
		done    : {expression: "done", format: function(data){return "job #" + data.job_id;}},
		fail    : {expression: "fail", format: function(data){return "job #" + data.job_id;}}
	}

	for(e in events)
	{
		init(e);
	}
	
	var level = {
			100 : {name: 'debug', class: 'label-success'},
			200 : {name: 'info', class: 'label-info'},
			300 : {name: 'warning', class: 'label-warning'},
			400 : {name: 'error', class: 'label-important'},
			500 : {name: 'critical', class: 'label-inverse'},
			550 : {name: 'alert', class: 'label-inverse'} 
	};

	var workers = {};

	var formatData = function(type, data){ 
		return {
			time: data.time, 
			relativeTime: moment(data.time).fromNow(), 
			action: type, 
			levelName: level[data.data.level].name, 
			levelClass: level[data.data.level].class, 
			detail: events[type].format(data.data),
			worker: data.data.worker,
			workerClass : data.data.worker.replace(new RegExp("(\\.|:)","gm"), ''),
			color: getColor(data)
		};
	}

	var getColor = function(data) {
					if (workers[data.data.worker] == undefined)
					{
							workers[data.data.worker] = colors[Object.keys(workers).length];
					}
					return workers[data.data.worker];
			};


	var colors = ['#1f77b4', '#aec7e8', '#ff7f0e', '#ffbb78', '#2ca02c', '#98df8a', 
	'#d62728', '#ff9896', '#9467bd', '#c5b0d5', '#8c564b', '#c49c94', '#e377c2', 
	'#f7b6d2', '#7f7f7f', '#c7c7c7', '#bcbd22', '#dbdb8d #', '17becf', '#9edae5'];

	
	/**
	 * Insert new events in the DOM
	 * 
	 */
	function appendLog(type, data)
	{
		if ($("input[data-rel="+level[data.data.level].name+"]").is(":checked"))
		{
			$( "#log-area" ).append(
				$("#log-template").render(formatData(type, data))
			);

			if (!counters.verbosity.hasOwnProperty(level[data.data.level].name))
			{
				addCounter("verbosity", level[data.data.level].name, $("#log-sweeper-form span[data-rel="+level[data.data.level].name+"]"));
			}

			if (!counters.type.hasOwnProperty([type]))
			{
				addCounter("type", type, $("#log-sweeper-form span[data-rel="+type+"]"));
			}

			incrCounter("verbosity", level[data.data.level].name, 1);
			incrCounter("type", type, 1);
			incrCounter("general", "g", 1);

			$("#log-area").find("time").each(function() {
				$(this).html(moment($(this).attr("title")).fromNow()).tooltip();
			});	
		}
	}

	function init(e)
	{
		var socket = new WebSocket("ws://"+serverIp+":1081/1.0/event/get");

		socket.onopen = function() {
			socket.send(JSON.stringify({
				"expression": events[e].expression,
				"start": formatISO(stop),
			}));
		};

		socket.onmessage = function(message) { 
			appendLog(e, JSON.parse(message.data));
		}; 
	}

	$("#clear-log-area").on("click", function(){
		$("#log-area").children().remove();
		resetCounters();
	});

	$("#log-filter-form").on("change", "input", function(e){

		var rel = $(this).data("rel")

		$("#log-area").append("<li class='filter-event'><b>"+ ($(this).is(":checked") ? "Start" : "Stop") +
			"</b> listening to <em>" + rel + "</em> events</li>");

		var mutedLevels = $.cookie('ResqueBoard.mutedLevel', {path: '/logs'});
		mutedLevels = mutedLevels.split(",");

		if ($(this).is(":checked")) {
			var index = mutedLevels.indexOf(rel);
			if (index != -1) {
				mutedLevels.splice(index, 1);
			}
		} else {
			mutedLevels[mutedLevels.length] = rel;
		}
		$.cookie('ResqueBoard.mutedLevel', mutedLevels.join(","), {expires: 365, path : '/logs'});

	});


	$("#log-sweeper-form").on("click", "button[data-rel=verbosity]", function(e){
		var toRemove = $("#log-area").children("li[data-verbosity="+$(this).data("level")+"]");
		updateCounters(toRemove);
		toRemove.remove();

		return false;
	});

	$("#log-sweeper-form").on("click", "button[data-rel=type]", function(e){
		var toRemove = $("#log-area").children("li[data-type="+$(this).data("type")+"]");
		updateCounters(toRemove);
		toRemove.remove();

		return false;
	});

	$("#log-area").on("mouseenter", "li", function(){
		$("#log-area li[data-worker="+$(this).data("worker")+"]")
			.addClass("hover-highlight");
	});

	$("#log-area").on("mouseleave", "li", function(){
		$("#log-area li[data-worker="+$(this).data("worker")+"]")
			.removeClass("hover-highlight");
	});


}

var Job = function()
{
	var totalJobs = 0;
	var jobsChartInit = 0;
	var jobsStats = {};

	return {
		initJobsChart : function(chartType) {
			if ($(".worker-stats").length != 0)
			{
				var getStaticStats = function(id)
					{
						if ($("#" + id).length == 0)
							return false;

						var $this = $("#" + id);
						var processedJobsCountDOM = $this.find('[data-status=processed]');
						var failedJobsCountDOM = $this.find('[data-status=failed]');
						return {
							processedJobsCountDOM : processedJobsCountDOM,
							failedJobsCountDOM : failedJobsCountDOM,
							processedJobsCount : parseInteger(processedJobsCountDOM.html()),
							failedJobsCount : parseInteger(failedJobsCountDOM.html()),
							chart: $this.find("[data-type=chart]")
						}
					};

				$(".worker-stats").each(function(data){
					var $this = $(this);
					var processedJobsCountDOM = $this.find('[data-status=processed]');
					var failedJobsCountDOM = $this.find('[data-status=failed]');
					var processedJobsCount = parseInteger(processedJobsCountDOM.html());
					var chartDOM = $this.find("[data-type=chart]");

					jobsStats[$this.attr("id")] = {
						processedJobsCountDOM: processedJobsCountDOM, 
						processedJobsCount: processedJobsCount, 
						failedJobsCountDOM: failedJobsCountDOM, 
						failedJobsCount : parseInteger(failedJobsCountDOM.html()),
						chart : chartDOM,
						chartType : chartDOM.data("chart-type") 
					};

					totalJobs += processedJobsCount;
				});

				jobsStats['global-worker-stats'] = getStaticStats('global-worker-stats');
				jobsStats['active-worker-stats'] = getStaticStats('active-worker-stats');

				jobsChartInit = 1;

				if (chartType == "pie")
					jobPieChart.init(jobsStats);
			}
			else jobsChartInit = 2;
		},

		updateJobsChart : function(workerId, level) { 
			if (jobsChartInit === 2) return;

			jobsStats[workerId]['processedJobsCount']++;
			totalJobs++;

			if (level == 400)
				jobsStats[workerId]['failedJobsCount']++;

			var updateCounter = function(workerId, success)
			{
				var index = "processedJobsCount";
				if (!success)
				{
					index = "failedJobsCount";
				}
				
				jobsStats[workerId][index + "DOM"].html(number_format(jobsStats[workerId][index]));

				if (jobsStats[workerId][index + "DOM"].queue("fx") == 0)
						jobsStats[workerId][index + "DOM"].effect("highlight");
				
					
			}

			
			// Refresh Counter
			if (level == 400)
			{
				updateCounter(workerId, false);
			} 

			updateCounter(workerId, true);

			if (jobsStats['active-worker-stats'] != false)
			{
				jobsStats['active-worker-stats']['processedJobsCount']++;
				updateCounter('active-worker-stats', true);
				
				if (level == 400)
				{
					jobsStats['active-worker-stats']['failedJobsCount']++;
					updateCounter('active-worker-stats', false);
				}

			}
			if (jobsStats['global-worker-stats'] != false)
			{
				jobsStats['global-worker-stats']['processedJobsCount']++;
				updateCounter('global-worker-stats', true);
			
				if (level == 400)
				{
					jobsStats['global-worker-stats']['failedJobsCount']++;
					updateCounter('global-worker-stats', false);	
				}
			}

			// Refresh Chart
			switch (jobsStats[workerId]['chartType'])
			{
				case "pie" :
					jobPieChart.redraw(jobsStats[workerId], true);
					jobPieChart.redraw(jobsStats['active-worker-stats'], false);
					jobPieChart.redraw(jobsStats['global-worker-stats'], false);
					break;
				case "horizontal-bar" : 
					for (_workerId in jobsStats) {
						if (jobsStats[_workerId] != false)
						{
							jobsStats[_workerId]['chart'].animate({
								width: Math.floor((jobsStats[_workerId]['processedJobsCount'] / totalJobs) * 100) + '%'
							}, 500);
						}
					};
			}
			
		}
	}	
}();


/**
 * Listen to workers activities in realtime
 * and update related counters
 * 
 * @return void
 */
function listenToWorkersJob(chartType) {

	var eventProcessor = function(){
		var getWorkerId = function(message) {
			return message.data.worker;
		}

		return {
			processDone : function(message){
				Job.updateJobsChart(
					getWorkerId(message).replace(new RegExp("(\\.|:)","gm"), ''), 
					message.data.level
				);
			},
			processGot : function(message){
				Job.updateJobsChart(
					getWorkerId(message).replace(new RegExp("(\\.|:)","gm"), ''), 
					message.data.level
				);
			},
			processFail : function(message){
				Job.updateJobsChart(
					getWorkerId(message).replace(new RegExp("(\\.|:)","gm"), ''), 
					message.data.level
				);
			}
		};
	}();


	// Start Listening to events 
	// *************************
	var events = {
		//got   : {expression: "got", format: function(data){return "job #" + data.job_id;}},
		//fork  : {expression: "fork", format: function(data){return "job #" + data.job_id;}},
		done  : {expression: "done", format: function(data){return "job #" + data.job_id;}},
		fail  : {expression: "fail", format: function(data){return "job #" + data.job_id;}}
	};

	for(e in events) {
		init(e);
	}

	Job.initJobsChart(chartType);

	function init(e) {
		var socket = new WebSocket("ws://"+serverIp+":1081/1.0/event/get");
		socket.onopen = function() {
			socket.send(JSON.stringify({
				"expression": events[e].expression,
				"start": formatISO(stop),
			}));
		};

		socket.onmessage = function(message) { 
			process(e, JSON.parse(message.data));
		};
	}

	// Process Messages
	// *************************

	function process(type, data) {
		switch(type) {
			case 'done' :
				eventProcessor.processDone(data);
				break;
			case 'fail' :
				eventProcessor.processFail(data);
				break;
			//case 'done' :
			//	eventProcessor.processDone(data);
		}
	}
}


var jobPieChart = function()
{
	var initData = function(d){

		var successCount = d['processedJobsCount'] - d['failedJobsCount'];

		var datas = [{
				name : "success", 
				count : (successCount == 0 && d['failedJobsCount'] == 0) ? 1 : successCount,
				color: "#aec7e8"
			}, {
				name : "failed", 
				count : d['failedJobsCount'],
				color : "#e7969c"
			}];
		return datas;
	}

	var m = 0;
	var z = d3.scale.category20c();

	var donut = d3.layout.pie().value(function(d){
					return d.count;
				});



	return {
		init : function(jobStats)
		{	
			for(domain in jobStats)
			{
				var data = initData(jobStats[domain]);

				var parent = jobStats[domain]['chart'];		

				// Define the margin, radius, and color scale. The color scale will be
				// assigned by index, but if you define your data using objects, you could pass
				// in a named field from the data object instead, such as `d.name`. Colors
				// are assigned lazily, so if you want deterministic behavior, define a domain
				// for the color scale.
				
				var r = (parent.width()-m*2)/2;
				var arc = d3.svg.arc().innerRadius(r / 2).outerRadius(r);

				// Insert an svg:svg element (with margin) for each row in our dataset. A
				// child svg:g element translates the origin to the pie center.
				var svg = d3.select(parent[0])
				.append("svg:svg")
				.attr("width", (r + m) * 2)
				.attr("height", (r + m) * 2)
				.append("svg:g")
				.attr("transform", "translate(" + (r + m) + "," + (r + m) + ")");

				// The data for each svg:svg element is a row of numbers (an array). We pass
				// that to d3.layout.pie to compute the angles for each arc. These start and end
				// angles are passed to d3.svg.arc to draw arcs! Note that the arc radius is
				// specified on the arc, not the layout.
				svg.selectAll("path")
				.data(donut(data))
				.enter().append("svg:path")
				.attr("d", arc)
				.attr("fill", function(d) { return d.data.color; })
				.attr("title", function(d){return d.data.count + " " +  d.data.name + " jobs"})
				.each(function(d) { this._current = d; })
				;
			}
		},
		redraw : function(stat)
		{
			var data = initData(stat);

			var parent = stat['chart'];	
			var r = (parent.width()-m*2)/2;
			var arc = d3.svg.arc().innerRadius(r / 2).outerRadius(r);

			d3.select(parent[0]).select("svg").selectAll("path")
			.data(donut(data))
			.transition()
			.duration(duration)
			.attrTween("d", arcTween)
			;

			function arcTween(a) {
				var i = d3.interpolate(this._current, a);
				this._current = i(0);
				return function(t) {
				return arc(i(t));
				};
			}
		}
	};
}();

function listenToWorkersActivities()
{
	var
		context = cubism.context().size(466),
		cube = context.cube("http://"+serverIp+":1081"),
		horizon = context.horizon().metric(cube.metric).height(53),
		rule = context.rule();

	var workersIds = [];
	var metrics = [];

	$(".worker-stats h4").each(function(i){ workersIds.push($(this).html());});

	if (workersIds.length == 0) return;

	for(index in workersIds)
	{
		metrics.push("sum(done.eq(worker,'"+workersIds[index]+"'))");
	}	

	d3.select("#worker-activities").append("div")
	.attr("class", "rule")
	.call(context.rule());

	d3.select("#worker-activities").selectAll(".horizon")
	.data(metrics)
	.enter().append("div")
	.attr("class", "horizon")
	.call(horizon.extent([-180, 180]).title(null));

	d3.select("#worker-activities").append("div")
	.attr("class", "axis")
	.call(context.axis().orient("bottom").ticks(d3.time.minutes, 10).tickSize(6,3,0)
	.tickFormat(d3.time.format("%H:%M")));

}


$(document).ready(function() {
	$('[data-event~=tooltip]').tooltip();
	$('[data-event~=collapse-all]').on('click', function(e){ e.preventDefault(); $('.collapse.in').collapse('hide'); });
	$('[data-event~=expand-all]').on('click', function(e){ e.preventDefault(); $('.collapse').not('.in').collapse('show'); });
	$('[data-event~=ajax-pagination]').on('change', 'select', function(e){window.location=$(this).val();});
});

function parseInteger(str)
{
	return +str.replace(',', '');
}

function number_format(x)
{
	return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");

}


function pieChart(id, total, data)
{
	var m = 0;
	var r = 80;
	var ir = 40;
	var textOffset = 14;
	var z = d3.scale.category20();

	var donut = d3.layout.pie().value(function(d){
					return d.count;
				});

	var parent = $("#"+id);	
	var w = parent.width();	
	var h = 250;

	console.log(data);

	// Define the margin, radius, and color scale. The color scale will be
	// assigned by index, but if you define your data using objects, you could pass
	// in a named field from the data object instead, such as `d.name`. Colors
	// are assigned lazily, so if you want deterministic behavior, define a domain
	// for the color scale.
	
	var arc = d3.svg.arc()
	.startAngle(function(d){ return d.startAngle; })
	.endAngle(function(d){ return d.endAngle; })
	.innerRadius(ir)
	.outerRadius(r);

	var formatCenterText = function(nb)
	{
		if (nb > 10000) return Math.round(nb /1000) + "K";
		return nb;
	}

	///////////////////////////////////////////////////////////
	// GROUP //////////////////////////////////////////////////
	///////////////////////////////////////////////////////////
	
	// Insert an svg:svg element (with margin) for each row in our dataset. A
	// child svg:g element translates the origin to the pie center.
	var svg = d3.select(parent[0])
	.append("svg:svg")
	.attr("class", "classyPieChart")
	.attr("width", w)
	.attr("height", h)
	;

	var arc_group = svg
	.append("svg:g")
	.attr("transform", "translate(" + (w/2) + "," + (h/2) + ")");

	var label_group = svg
	.append("svg:g")
	.attr("transform", "translate(" + (w/2) + "," + (h/2) + ")");

	var center_group = svg
	.append("svg:g")
	.attr("transform", "translate(" + (w/2) + "," + (h/2) + ")");

	///////////////////////////////////////////////////////////
	// ARC ////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////

	// The data for each svg:svg element is a row of numbers (an array). We pass
	// that to d3.layout.pie to compute the angles for each arc. These start and end
	// angles are passed to d3.svg.arc to draw arcs! Note that the arc radius is
	// specified on the arc, not the layout.
	arc_group.selectAll("path")
		.data(donut(data))
		.enter().append("svg:path")
		.attr("d", arc)
		.attr("stroke", "white")
		.attr("stroke-width", 0.5)
		.attr("fill", function(d, i) { return z(i); })
		.each(function(d, i) { data[i].startAngle = d.startAngle; data[i].endAngle = d.endAngle; })
	;

	///////////////////////////////////////////////////////////
	// PLACEHOLDER ////////////////////////////////////////////
	///////////////////////////////////////////////////////////
	
	if (data.length == 0) {
		var paths = arc_group.append("svg:circle")
	    .attr("fill", "#EFEFEF")
	    .attr("r", r);

	    var whiteCircle = center_group.append("svg:circle")
			.attr("fill", "white")
			.attr("r", ir);
	}

	///////////////////////////////////////////////////////////
	// CENTER TEXT ////////////////////////////////////////////
	///////////////////////////////////////////////////////////

	

	// // "TOTAL" LABEL
	var totalLabel = center_group.append("svg:text")
		.attr("class", "pie-label")
		.attr("dy", -15)
		.attr("text-anchor", "middle") // text-align: right
		.text("TOTAL")
	;

	//TOTAL TRAFFIC VALUE
	var totalValue = center_group.append("svg:text")
			.attr("class", "pie-total")
			.attr("dy", 7)
			.attr("text-anchor", "middle") // text-align: right
			.text(formatCenterText(total))
	;

	//UNITS LABEL
	var totalUnits = center_group.append("svg:text")
			.attr("class", "pie-units")
			.attr("dy", 21)
			.attr("text-anchor", "middle") // text-align: right
			.text("jobs")
	;

	///////////////////////////////////////////////////////////
	// ARC LABEL //////////////////////////////////////////////
	///////////////////////////////////////////////////////////

	//DRAW TICK MARK LINES FOR LABELS
	var lines = label_group.selectAll("line").data(data);
	lines.enter().append("svg:line")
	.attr("x1", 0)
	.attr("x2", 0)
	.attr("y1", -r-3)
	.attr("y2", -r-8)
	.attr("stroke", "gray")
	.attr("transform", function(d) { 
		return "rotate(" + (d.startAngle+d.endAngle)/2 * (180/Math.PI) + ")";
	});

	//DRAW LABELS WITH PERCENTAGE VALUES
	var valueLabels = label_group.selectAll("text.pie-value").data(data)
		.attr("dy", function(d){
			if ((d.startAngle+d.endAngle)/2 > Math.PI/2 && (d.startAngle+d.endAngle)/2 < Math.PI*1.5 ) {
				return 5;
			} else {
				return -7;
			}
		})
		.attr("text-anchor", function(d){
			if ( (d.startAngle+d.endAngle)/2 < Math.PI ){
				return "beginning";
			} else {
				return "end";
			}
		})
		.enter().append("svg:text")
		.attr("class", "pie-value")
		.attr("transform", function(d) {
			return "translate(" + Math.cos(((d.startAngle+d.endAngle - Math.PI)/2)) * (r+textOffset) + "," + Math.sin((d.startAngle+d.endAngle - Math.PI)/2) * (r+textOffset) + ")";
		})
		.attr("dy", function(d){
			if ((d.startAngle+d.endAngle)/2 > Math.PI/2 && (d.startAngle+d.endAngle)/2 < Math.PI*1.5 ) {
				return 5;
			} else {
				return -7;
			}
		})
		.attr("text-anchor", function(d){
			if ( (d.startAngle+d.endAngle)/2 < Math.PI ){
				return "beginning";
			} else {
				return "end";
			}
		}).text(function(d){
			return d.count + "%";
		})
	;

	//DRAW LABELS WITH ENTITY NAMES
	var nameLabels = label_group
		.selectAll("text.pie-units")
		.data(data)
		.attr("dy", function(d){
			if ((d.startAngle+d.endAngle)/2 > Math.PI/2 && (d.startAngle+d.endAngle)/2 < Math.PI*1.5 ) {
				return 17;
			} else {
				return 5;
			}
		})
		.attr("text-anchor", function(d){
			if ((d.startAngle+d.endAngle)/2 < Math.PI ) {
				return "beginning";
			} else {
				return "end";
			}
		}).text(function(d){
			return d.name;
		})
	;

	nameLabels.enter().append("svg:text")
		.attr("class", "pie-units")
		.attr("transform", function(d) {
			return "translate(" + Math.cos(((d.startAngle+d.endAngle - Math.PI)/2)) * (r+textOffset) + "," + Math.sin((d.startAngle+d.endAngle - Math.PI)/2) * (r+textOffset) + ")";
		})
		.attr("dy", function(d){
			if ((d.startAngle+d.endAngle)/2 > Math.PI/2 && (d.startAngle+d.endAngle)/2 < Math.PI*1.5 ) {
				return 17;
			} else {
				return 5;
			}
		})
		.attr("text-anchor", function(d){
			if ((d.startAngle+d.endAngle)/2 < Math.PI ) {
				return "beginning";
			} else {
				return "end";
			}
		}).text(function(d){
			return d.name;
		})
	;

}
