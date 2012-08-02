
var formatISO = function(date){
	var format = d3.time.format.iso;
	return format.parse(date); // returns a Date
}

var stop = new Date(Date.now());

var limit = 25;
var duration = 1500;
var step = {second : 10, code : "1e4"};

function jobsActivities()
{
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

			/*
			Find the next time after the last entry
			because the last entry doesn't have a x axis
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

			barArea.selectAll("rect")
			.data(data)
			.enter()
			.append("rect")
			.attr("x", function(d) { return x(d.time) + barGutter; })
			.attr("y", function(d) { return y(d.value); })
			.attr("width", barWidth)
			.attr("height", function(d) { return height - y(d.value);  })
			.attr("title", function(d){return d.value;})
			.attr("data-target", "#job-details-modal")
			.on("click", function(d){
				
				displayJobsModal(d.time);
			})
			;

			barArea.selectAll("text").data(data).enter().append("text")
			.attr("x", function(d) { return x(d.time) + barWidth + barGutter; })
			.attr("y", function(d) { return ((height-y(d.value))>barLabelHeight ? y(d.value) : (y(d.value) - barLabelHeight)); })
			.attr("text-anchor", "middle")
			.attr("class", function(d){if ((height-y(d.value))<=barLabelHeight){ return "bar-label out";} return "bar-label";})
			.attr("dx", -barWidth/2 - barGutter)
			.attr("dy", "1.5em")
			.text(function(d){return d.value;})
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
			.attr("x", function(d, i) { return x(getNextTick(d.time)); })
			.attr("y", function(d) { return y(d.value); })
			.attr("width", barWidth)
			.attr("clip-path", "url(#clip)")
			.attr("height", function(d) { return height - y(d.value); })
			.on("click", function(d){ alert(d.time); })
			.transition()
			.duration(duration)
			.attr("x", function(d) { return x(d.time) + barWidth + 2; })
			;

			
			rect.transition()
			.duration(duration)
			.attr("clip-path", "url(#clip)")
			.attr("x", function(d) { return x(d.time) + barGutter; })
			.attr("y", function(d) { return y(d.value); })
			.attr("height", function(d) { return height - y(d.value); })
			;

			rect.exit().transition()
			.duration(duration)
			.attr("x", function(d, i) { return x(d.time) + barGutter; })
			.remove()
			;


			// TEXT
			// *****
			text.enter().insert("text")
			.attr("x", function(d) { return x(getNextTick(getNextTick(d.time))); })
			.attr("y", function(d) { return ( (height-y(d.value))>barLabelHeight ? y(d.value) : (y(d.value) - barLabelHeight)); })
			.attr("dx", barWidth/2 - barGutter)
			.attr("text-anchor", "middle")
			.attr("fill", "black")
			.attr("class", function(d){if ((height-y(d.value))<=15){return "bar-label out";}  return "bar-label";})
			.attr("dx", -barWidth/2 - barGutter)
			.attr("dy", "1.5em")
			.text(function(d){return d.value;})
			.transition()
			.duration(duration)
			.attr("x", function(d) { return x(d.time) + barWidth + barGutter; })
			;


			text.transition()
			.duration(duration)
			.attr("x", function(d) { return x(d.time) + barWidth + barGutter; })
			.attr("y", function(d) { return ( (height-y(d.value))>barLabelHeight ? y(d.value) : (y(d.value) - barLabelHeight)); })
			.attr("class", function(d){if ( (height-y(d.value))<=barLabelHeight){ return "bar-label out";} return "bar-label";})
			;


			text.exit().transition()
			.duration(duration)
			.attr("x", function(d) { return x(d.time) + barGutter; })
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
				"step" : step.code // 10 seconds
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
						"step" : step.code // 10 seconds
					}))
				}, step.second * 1000);
			}
		};
	});

}

$(document).ready(function() {
	$(".chart-pie").each(function(i){
		var data = [{name : "successfull", count : $(this).data("success"), color: "#aec7e8"}, {name : "failed", count : $(this).data("failed"), color : "#e7969c"}];
		pieChart(this, data);
	});

	$('[rel=tooltip]').tooltip();
});



function displayJobsModal(startTime)
{
	startTimeStamp = (Date.parse(startTime))/1000;

	$.ajax({
		url : "/api/jobs/" + startTimeStamp + "/" + (startTimeStamp + step.second),
		success : function(message){
			
			var modalTimestamp = $("#job-details-modal").data("timestamp");
			if (modalTimestamp != startTimeStamp)
			{

				$("#job-details-modal .modal-body").html(
					$("#jobs-tpl").render(message)
				);
				$("#job-details-modal .modal-header .badge").html(message.length);
			}

			


			$("#job-details-modal").modal('show');
		}
	});

	
}


function pieChart(parent, data) {


	var donut = d3.layout.pie().value(function(d){
		return d.count;
	});

// Define the margin, radius, and color scale. The color scale will be
// assigned by index, but if you define your data using objects, you could pass
// in a named field from the data object instead, such as `d.name`. Colors
// are assigned lazily, so if you want deterministic behavior, define a domain
// for the color scale.
var m = 10,
r = ($(parent).width()-m*2)/2 ,
z = d3.scale.category20c();

// Insert an svg:svg element (with margin) for each row in our dataset. A
// child svg:g element translates the origin to the pie center.
var svg = d3.select(parent)

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
.attr("d", d3.svg.arc()
	.innerRadius(r / 2)
	.outerRadius(r))
.style("fill", function(d) { return d.data.color; })
.attr("title", function(d){return d.data.count + " " +  d.data.name + " jobs"})

;

}



function loadLogs()
{

	var counters = {
		general : {g: $("[rel=log-counter]")},
		type : {},
		verbosity : {}
	};

	var addCounter = function(cat, type, dom) {
			counters[cat][type] = dom;
	};

	var incrCounter = function(cat, type, step) {
			var node = counters[cat][type];
			node.html(parseInt(node.html()) + step).effect("highlight");
	};

	var decrCounter = function(cat, type, step) {
			var node = counters[cat][type];
			var count = parseInt(node.html());
			if (count - step >= 0) node.html(count - step).effect("highlight");
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
		done    : {expression: "done", format: function(data){return "job #" + data.job_id;}}
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


	var colors = ['#1f77b4', '#aec7e8', '#ff7f0e', '#ffbb78', '#2ca02c', '#98df8a', '#d62728', '#ff9896', '#9467bd', '#c5b0d5', '#8c564b', '#c49c94', '#e377c2', '#f7b6d2', 
	'#7f7f7f', '#c7c7c7', '#bcbd22', '#dbdb8d #', '17becf', '#9edae5'];

	

	function appendLog(type, data)
	{
		if ($("input[rel="+level[data.data.level].name+"]").is(":checked"))
		{
			$( "#log-area" ).append(
				$("#log-template").render(formatData(type, data))
			);

			if (!counters.verbosity.hasOwnProperty(level[data.data.level].name))
			{
				addCounter("verbosity", level[data.data.level].name, $("#log-sweeper-form span[rel="+level[data.data.level].name+"]"));
			}

			if (!counters.type.hasOwnProperty([type]))
			{
				addCounter("type", type, $("#log-sweeper-form span[rel="+type+"]"));
			}

			incrCounter("verbosity", level[data.data.level].name, 1);
			incrCounter("type", type, 1);
			incrCounter("general", "g", 1);

			$("#log-area").find("date").each(function() {
				$(this).html(moment($(this).attr("title")).fromNow());
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

		var rel = $(this).attr("rel")

		$("#log-area").append("<li class='filter-event'><b>"+ ($(this).is(":checked") ? "Start" : "Stop") +
			"</b> listening to <em>" + rel + "</em> events</li>");

		var mutedLevels = $.cookie('ResqueBoard.mutedLevel');
		mutedLevels = mutedLevels.split(",");

		if ($(this).is(":checked")) {
			var index = mutedLevels.indexOf(rel);
			if (index != -1) {
				mutedLevels.splice(index, 1);
			}
		} else {
			mutedLevels[mutedLevels.length] = rel;
		}
		$.cookie('ResqueBoard.mutedLevel', mutedLevels.join(","));

	});


	$("#log-sweeper-form").on("click", "button[rel=verbosity]", function(e){
		var toRemove = $("#log-area").children("li[data-verbosity="+$(this).data("level")+"]");
		updateCounters(toRemove);
		toRemove.remove();

		return false;
	});

	$("#log-sweeper-form").on("click", "button[rel=type]", function(e){
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



function listenToWorkersJob() {

	var eventProcessor = function(){

		var getWorkerId = function(message) {
			return message.data.worker;
		}

		return {

			processDone : function(message){
				
				// Update DOM
				var counter = '';
				var cleanWorkerId = getWorkerId(message).replace(new RegExp("(\\.|:)","gm"), '');
		
				if (message.data.level == 400) {
					counter = $("#f_" + cleanWorkerId);
					$("#f_totalJobCount").html(parseInt($("#f_totalJobCount").html())+1).effect("highlight");
					$("#f_activeWorkersJobCount").html(parseInt($("#f_activeWorkersJobCount").html())+1).effect("highlight");
				}
				else {
					counter = $("#s_" + cleanWorkerId);
				}
				
				$("#totalJobCount").html(parseInt($("#totalJobCount").html())+1).effect("highlight");
				$("#activeWorkersJobCount").html(parseInt($("#activeWorkersJobCount").html())+1).effect("highlight");

				counter.html(parseInt(counter.html()) + 1).effect("highlight");
			}
		};

	}();


	// Start Listening to events 
	// *************************

	var events = {
		got   : {expression: "got", format: function(data){return "job #" + data.job_id;}},
		fork  : {expression: "fork", format: function(data){return "job #" + data.job_id;}},
		done  : {expression: "done", format: function(data){return "job #" + data.job_id;}}
	};

	for(e in events) {
		init(e);
	}

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
			case 'got' :
				//eventProcessor.processGot(data);
				break;
			case 'fork' :
				//eventProcessor.processFork(data);
				break;
			case 'done' :
				eventProcessor.processDone(data);
		}
	}
}




