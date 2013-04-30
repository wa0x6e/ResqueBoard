/**
 * ResqueBoard Javascript File
 *
 * Make all the numbers on the screen blink
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author		Wan Qi Chen <kami@kamisama.me>
 * @copyright	Copyright 2012, Wan Qi Chen <kami@kamisama.me>
 * @link		http://resqueboard.kamisama.me
 * @since		1.0.0
 * @license		MIT License (http://www.opensource.org/licenses/mit-license.ctp)
 */

	$("[data-event~=tooltip]").tooltip();
	$("[data-event~=collapse-all]").on("click", function(e){ e.preventDefault(); $(".collapse.in").collapse("hide"); });
	$("[data-event~=expand-all]").on("click", function(e){ e.preventDefault(); $(".collapse").not(".in").collapse("show"); });

	$(".infinite-scroll").infinitescroll({
		navSelector	: "ul.pager",
		nextSelector : "ul.pager li.next a",
		itemSelector : ".infinite-scroll li",
		loading: {
			finishedMsg: "No more pages to load.",
			img: "http://www.infinite-scroll.com/loading.gif"
		},
		bufferPx: 5000
	});

	/**
	 * Use a form select's options as navigation
	 */
	$(".navigator").on("change", "select", function () {
		window.location = $("option", this).filter(":selected").val();
	});

	// Init syntax highlighter
	hljs.initHighlightingOnLoad();


	var stop = new Date(Date.now());

	/**
	 * Convert a date to ISO 8601 format
	 * @param	Date date A date object
	 * @return string An ISO 8601 formatted date
	 */
	var formatISO = function(date) {
		var format = d3.time.format.iso;
		return format.parse(date); // returns a Date
	};

	/**
	 * Duration of the transition animation
	 * @type int
	 */
	var duration = 1500;

	/**
	 * Datas about the polling delay
	 * @type Object
	 */
	var step = new Array(
		{second : 10,		code : "1e4"},
		{second : 60,		code : "6e4"},
		{second : 300,		code : "3e5"},
		{second : 3600,		code : "36e5"},
		{second : 84600,	code : "864e5"}
	);

	/**
	 * Display latest jobs stats
	 *
	 * @return void
	 */
	function listenToJobsActivities($scope, $http)
	{
		/**
		 * Number of division to display
		 * @type int
		 */
		var limit = 80;

		d3.json("http://"+CUBE_URL+"/1.0/metric/get"+
			"?expression=sum(got)"+
			"&start="+encodeURIComponent("2012-07-07T16:00:00Z")+
			"&stop=" + encodeURIComponent(formatISO(stop))+
			"&limit=" + limit +
			"&step=" + step[0].code, function(data)
			{


				var
					margin = {top:25, right:35, bottom: 35, left: 20},
					width = 860 - margin.right - margin.left,
					height = 180 - margin.top - margin.bottom,
					barHeight = height,
					barWidth = width/limit - 2,
					barGutter = 0,
					barLabelHeight = 20
				;

				/**
				 * Find the next time after the last entry
				 * because the last entry doesn"t have a x axis
				 */
				var getNextTick = function(date)
				{
					date = new Date(date);
					return new Date(date.setSeconds(date.getSeconds() + step[0].second));
				};

				data = data.map(function(d){return {time:new Date(d.time), value:	d.value};}).slice(0, limit);

				var x = d3.time.scale()
					.domain([data[0].time, getNextTick(data[data.length-1].time)])
					.range([0,width])
				;

				var y = d3.scale.linear()
					.domain([0,d3.max(data.map(function(d){return d.value;}))])
					.rangeRound([height,0])
				;


				var chart = d3.select("#lastest-jobs").append("svg")
					.attr("class", "chart")
					.attr("width", width + margin.left + margin.right)
					.attr("height", height + margin.top + margin.bottom)
					.append("g")
					.attr("transform", "translate(" + margin.left	+ "," + margin.top + ")")
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
					.text("time")
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
					.attr("data-target", ".modal")
					.on("click", function(d){
						$scope.viewJobs(d.time);
					})
					.call(barDim)
					;
				};

				var barDim = function(selection)
				{
					selection
					.attr("x", function(d) { return x(d.time) + barGutter; })
					.attr("y", function(d) { return y(d.value); })
					.attr("width", barWidth)
					.attr("height", function(d) { return height - y(d.value);	})
					;
				};


				barArea.selectAll("rect")
					.data(data)
					.enter()
					.append("rect")
					.call(barProp)
				;

				var barLabelDim = function(selection)
				{
					selection
					.attr("y", function(d) { return ((height-y(d.value))>barLabelHeight ? y(d.value) : (y(d.value) - barLabelHeight)); })
					.attr("dy", "1.5em")
					.attr("x", function(d) { return x(d.time) + barWidth/2; })
					;
				};

				var xAxis = d3.svg.axis()
					.scale(x)
					//.ticks(d3.time.seconds, 30)
					//.tickFormat(d3.time.format("%M:%S"))
					.tickSubdivide(2)
					.tickSize(6,3,0)
					.orient("bottom")
				;

				var yAxis = d3.svg.axis()
					.scale(y)
					.tickSize(6,3,0)
					.orient("right")
					.ticks(4)
				;

				var xAxisParent = chart.append("g")
					.attr("transform", "translate(0," + height + ")")
					.attr("class", "x-axis")
					.call(xAxis)
				;

				var yAxisParent = chart.append("g")
					.attr("class", "y-axis")
					.attr("transform", "translate(" + width + ",0)")
					.call(yAxis)
				;


				function redraw() {

					x.domain([data[0].time, getNextTick(data[data.length-1].time)]);
					y.domain([0,d3.max(data.map(function(d){return d.value;}))]);

					var rect = barArea.selectAll("rect").data(data, function(d){return d.time;});

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

					// AXIS
					// *****
					xAxisParent.transition()
						.duration(duration)
						.call(xAxis)
					;

					yAxisParent.transition()
						.duration(duration)
						.call(yAxis)
					;

				}

				var metricSocket = new WebSocket("ws://"+CUBE_URL+"/1.0/metric/get");

				metricSocket.onopen = function() {
					var nextDate = getNextTick(data[data.length-1].time);

					metricSocket.send(JSON.stringify({
						"expression": "sum(got)",
						"start" : nextDate.toISOString(),
						"stop": getNextTick(nextDate).toISOString(),
						"limit": 1,
						"step" : step[0].code
					}));
				};

				metricSocket.onmessage = function(message) {
					var JsonData = JSON.parse(message.data);

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
								"step" : step[0].code
							}));
						}, step[0].second * 1000);
					}
				};
			}
		);
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

		};

		var decrCounter = function(cat, type, step) {
				var node = counters[cat][type];
				var count = parseInteger(node.html());
				if (count - step >= 0)
				{
					node.html(count - step);
					fireEffect(node, "highlight");
				}
		};

		// Update counters, by passing a list of nodes to be removed
		var updateCounters = function(nodeList)
		{

			nodeList.each(function(){

				var v = $(this);

				decrCounter("type", v.data("type"), 1);
				decrCounter("verbosity", v.data("verbosity"), 1);
				decrCounter("general", "g", 1);

			});
		};

		var resetCounters = function()
		{
			for(var cat in counters) {
				if (counters.hasOwnProperty(cat)) {
					for (var type in counters[cat]) {
						if (counters[cat].hasOwnProperty(type)) {
							counters[cat][type].html(0);
						}
					}
				}
			}
		};

		var events = {
			sleep	: {expression: "sleep", format: function(data){
				return "for " + data.second + " seconds";}},
			got		: {expression: "got", format: function(data){
				return "job <a href=\"/jobs/view?job_id="+data.args.payload.id+"\" rel=\"contents\" title=\"View job details\">#" + data.args.payload.id + "</a>";}},
			process : {expression: "process", format: function(data){
				return "job <a href=\"/jobs/view?job_id="+data.job_id+"\" rel=\"contents\" title=\"View job details\">#" + data.job_id + "</a>";}},
			fork	: {expression: "fork", format: function(data){
				return "job <a href=\"/jobs/view?job_id="+data.job_id+"\" rel=\"contents\" title=\"View job details\">#" + data.job_id + "</a>";}},
			done	: {expression: "done", format: function(data){
				return "job <a href=\"/jobs/view?job_id="+data.job_id+"\" rel=\"contents\" title=\"View job details\">#" + data.job_id + "</a>";}},
			fail	: {expression: "fail", format: function(data){
				return "job <a href=\"/jobs/view?job_id="+data.job_id+"\" rel=\"contents\" title=\"View job details\">#" + data.job_id + "</a>";}},
			start	: {expression: "start", format: function(data){
				return "worker #" + data.worker;}},
			stop	: {expression: "shutdown", format: function(data){
				return "worker #" + data.worker;}},
			pause	: {expression: "pause", format: function(data){
				return "worker #" + data.worker;}},
			resume	: {expression: "resume", format: function(data){
				return "worker #" + data.worker;}},
			prune	: {expression: "prune", format: function(data){
				return "worker #" + data.worker;}}
		};

		for(var i in events)
		{
			if (events.hasOwnProperty(i)) {
				init(i);
			}
		}

		var level = {
				100 : {name: "debug",	classStyle: "label-success"},
				200 : {name: "info",	classStyle: "label-info"},
				300 : {name: "warning", classStyle: "label-warning"},
				400 : {name: "error",	classStyle: "label-important"},
				500 : {name: "critical", classStyle: "label-inverse"},
				550 : {name: "alert",	classStyle: "label-inverse"}
		};

		var workers = {};

		var formatData = function(type, data){
			return {
				time: data.time,
				hourTime: moment(data.time).format("H:mm:ss"),
				action: type,
				levelName: level[data.data.level].name,
				levelClass: level[data.data.level].classStyle,
				detail: events[type].format(data.data),
				worker: data.data.worker,
				workerClass : cleanWorkerName(data.data.worker),
				color: getColor(data)
			};
		};

		var getColor = function(data) {
			if (workers[data.data.worker] === undefined)
			{
					workers[data.data.worker] = colors[Object.keys(workers).length];
			}
			return workers[data.data.worker];
		};


		var colors = ["#1f77b4", "#aec7e8", "#ff7f0e", "#ffbb78", "#2ca02c", "#98df8a",
		"#d62728", "#ff9896", "#9467bd", "#c5b0d5", "#8c564b", "#c49c94", "#e377c2",
		"#f7b6d2", "#7f7f7f", "#c7c7c7", "#bcbd22", "#dbdb8d #", "17becf", "#9edae5"];


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

				if (!counters.verbosity.hasOwnProperty(level[data.data.level].name)) {
					addCounter("verbosity", level[data.data.level].name, $("#log-sweeper-form span[data-rel="+level[data.data.level].name+"]"));
				}

				if (!counters.type.hasOwnProperty([type])) {
					addCounter("type", type, $("#log-sweeper-form span[data-rel="+type+"]"));
				}

				incrCounter("verbosity", level[data.data.level].name, 1);
				incrCounter("type", type, 1);
				incrCounter("general", "g", 1);
			}
		}

		function init(e)
		{
			var socket = new WebSocket("ws://"+CUBE_URL+"/1.0/event/get");

			socket.onopen = function() {
				socket.send(JSON.stringify({
					"expression": events[e].expression,
					"start": formatISO(stop)
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

			var rel = $(this).data("rel");

			$("#log-area").append("<li class=\"filter-event\"><b>"+ ($(this).is(":checked") ? "Start" : "Stop") +
				"</b> listening to <em>" + rel + "</em> events</li>");

			var mutedLevels = $.cookie("ResqueBoard.mutedLevel", {path: "/logs"});
			mutedLevels = mutedLevels.split(",");

			if ($(this).is(":checked")) {
				var index = mutedLevels.indexOf(rel);
				if (index !== -1) {
					mutedLevels.splice(index, 1);
				}
			} else {
				mutedLevels[mutedLevels.length] = rel;
			}
			$.cookie("ResqueBoard.mutedLevel", mutedLevels.join(","), {expires: 365, path : "/logs"});

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




	}




	/**
	 * Create a pie chart from a set of data
	 *
	 * @param	{[type]} id		[description]
	 * @param	{[type]} total	[description]
	 * @param	{[type]} data	[description]
	 */
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

		var formatCenterText = function(nb) {
			if (nb > 10000) {
				return Math.round(nb /1000) + "K";
			}
			return nb;
		};

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
			.attr("transform", "translate(" + (w/2) + "," + (h/2) + ")")
		;

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

		if (data.length === 0) {
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




/**
 *
 */
function initJobsOverview() {

	var animationDuration = 500; // in ms

	var containerDom = $("#chart");

	var page = function() {

		var margin_top = 20;
		var margin_right = 45;
		var margin_bottom = 35;
		var margin_left = 35;
		var w = containerDom.width();
		var h = containerDom.height();

		var graphItems = {};
		var emptyData = [];

		var svg = d3.select("#chart").append("svg")
			.attr("width", w)
			.attr("height", h)
		;

		var placeholder = svg.append("text")
			.attr("x", w/2)
			.attr("y", h/2)
			.attr("text-anchor", "center")
			.text("Loading …")
		;

		var xAxisParent = svg.append("g")
			.attr("class", "x-axis")
			.attr("transform", "translate(" + margin_left + "," + (h - margin_bottom) + ")")

		;

		var yAxisParentLeft = svg.append("g")
			.attr("class", "y-axis")
			.attr("transform", "translate(" + (margin_left-5) + "," + margin_top + ")")
		;

		var yAxisParentRight = svg.append("g")
			.attr("class", "y-axis right")
			.attr("transform", "translate(" + (w-margin_right + 5) + "," + margin_top + ")")
		;

		var xAxis = d3.svg.axis()
			.orient("bottom")
			.tickSize(-h + margin_top - 5 + margin_bottom, 3, 0)
			.tickPadding(7)
		;

		var yAxisLeft = d3.svg.axis()
			.tickSize(-w + margin_left + margin_right - 5, 3, 0)
			.orient("left")
			.tickFormat(d3.format("s"))
			.tickPadding(7)
		;

		var yAxisRight = d3.svg.axis()
			.orient("right")
			.tickPadding(7)
		;

		var axisGroup = {
			"axis" : {
				"left" : yAxisLeft,
				"right" : yAxisRight,
				"bottom" : xAxis
			},
			"max" : {
				"left": {},
				"right": {}
			}
		};


		var graph_group = svg.append("g")
			.attr("width", w - margin_left + margin_right)
			.attr("height", h - margin_top + margin_bottom)
			.attr("transform", "translate(" + margin_left + "," + margin_top + ")")
		;

		svg.append("text")
			.attr("x", -90)
			.attr("y", margin_left + 10)
			.attr("transform", "rotate(-90)")
			.style("text-anchor", "left")
			.text("Jobs number")
			.attr("class", "graph-legend")
		;

		svg.append("text")
			.attr("x", -margin_top)
			.attr("y", w - margin_left - 10)
			.attr("transform", "rotate(-90)")
			.style("text-anchor", "end")
			.text("Processing time in ms")
			.attr("class", "graph-legend")
		;

		var init = function(dom) {

			var startDate = dom.data("startDate");
			var endDate = dom.data("endDate");
			var dataStep = dom.data("step");

			d3.json("http://"+CUBE_URL+"/1.0/metric/get"+
			"?expression=sum(got)" +
			"&start="+ encodeURIComponent(startDate) +
			"&stop=" + encodeURIComponent(endDate) +
			"&step=" + dataStep, function(data)
			{

				placeholder.remove();

				emptyData = data.map(function(d){return {time: new Date(d.time), value: 0};});
				data = data.map(function(d){return {time: new Date(d.time), value: d.value};});

				axisGroup.max.left.processed = {
					"value": d3.max(data.map(function(d){return d.value;})),
					"status": 1
				};

				var graphLine = graph_group.append("path")
					.datum(data)
					.attr("class", "graph-line")
					.attr("id", "g-line-processed")
				;

				var graphArea = graph_group.append("path")
					.datum(data)
					.attr("class", "graph-area")
					.attr("id", "g-area-got")
				;

				graphItems.processed = {
					"line": graphLine,
					"area": graphArea,
					"data": data,
					"yAxis": "left"
				};

				redraw(graphItems.processed);


			});
		};



		var displayLine = function(start, end, dataStep, expression, id, axis) {

			if (graphItems.hasOwnProperty(id)) {

				axisGroup.max[axis][id].status = 1;
				graphItems[id].line.datum(graphItems[id].data);
				graphItems[id].area.datum(graphItems[id].data);
				graphItems[id].line.transition().duration(animationDuration).style("opacity", 1);
				redraw(graphItems[id]);
				return;
			}

			d3.json("http://"+CUBE_URL+"/1.0/metric/get"+
			"?expression=" + expression +
			"&start="+ encodeURIComponent(start) +
			"&stop=" + encodeURIComponent(end) +
			"&step=" + dataStep, function(data)
			{

				data = data.map(function(d){return {time:new Date(d.time), value:	d.value};});


				axisGroup.max[axis][id] = {
					"value": d3.max(data.map(function(d){ return d.value;})),
					"status": 1
				};

				var graphLine = graph_group.append("path")
					.datum(data)
					.attr("class", "graph-line")
					.attr("id", "g-line-" + id)
				;

				var graphArea = graph_group.append("path")
					.attr("class", "graph-area")
					.attr("id", "g-area-" + id)
				;

				if (axis !== "right") {
					graphArea.datum(data);
				} else {
					graphArea.datum(emptyData);
				}

				graphItems[id] = {
					"line": graphLine,
					"area": graphArea,
					"data": data,
					"yAxis": axis
				};

				redraw(graphItems[id]);

			});
		};

		var hideLine = function(id) {
			if (graphItems.hasOwnProperty(id)) {

				graphItems[id].line.datum(emptyData);
				graphItems[id].area.datum(emptyData);

				axisGroup.max[graphItems[id].yAxis][id].status = 0;
				redraw(graphItems[id]);
			}
		};

		var redraw = function(graphItem) {

			// Redraw Y axis
			var yScale = d3.scale.linear()
				.domain([0, d3.max(d3.values(axisGroup.max[graphItem.yAxis]).map(function(d){ return d.status === 1 ? d.value : 0;}))*1.25])
				.range([h - margin_top - margin_bottom, 0]);

			var xScale = d3.time.scale()
				.domain([graphItem.data[0].time, graphItem.data[graphItem.data.length-1].time])
				.range([0, w - margin_left - margin_right]);


			var area = d3.svg.area()
				.x(function(d) { return xScale(d.time); })
				.y0(function(d) { return h - margin_bottom - margin_top; })
				.y1(function(d) { return yScale(d.value); })
			;

			var line = d3.svg.line()
				.x(function(d) {return xScale(d.time);})
				.y(function(d) {return yScale(d.value);})
			;

			function redrawLine(selection) {
				selection.transition().duration(animationDuration).attr("d", line);
			}

			function redrawGraph(itemIndex) {

				if (graphItems[itemIndex].yAxis !== graphItem.yAxis) {
					return;
				}

				graphItems[itemIndex].area.transition().duration(animationDuration).attr("d", area);

					if (axisGroup.max[graphItems[itemIndex].yAxis][itemIndex].status === 0) {
						graphItems[itemIndex].line.call(redrawLine);
						graphItems[itemIndex].line.transition().delay(animationDuration).duration(100).style("opacity", 0);

					} else {
						graphItems[itemIndex].line.style("opacity", 1);
						graphItems[itemIndex].line.call(redrawLine);
					}
			}

			for (var item in graphItems) {
				if (graphItems.hasOwnProperty(item)) {
					redrawGraph(item);
				}
			}

			xAxis.scale(xScale);
			axisGroup.axis[graphItem.yAxis].scale(yScale);

			if (graphItem.yAxis === "left") {
				yAxisParentLeft.transition().duration(animationDuration).call(axisGroup.axis[graphItem.yAxis]);
			} else {
				yAxisParentRight.transition().duration(animationDuration).call(axisGroup.axis[graphItem.yAxis]);
			}
			xAxisParent.transition().duration(animationDuration).call(xAxis);
		};

		init($("#date-range .active a"));

		$("#type-range").on("click", "a", function(e) {
			e.preventDefault();

			var dom = $(this);
			var icon = $(this).parent().find("i");

			if (dom.hasClass("active")) {
				hideLine(dom.data("type"));
				dom.removeClass("active");
				icon.removeClass("icon-check");
				icon.addClass("icon-check-empty");
			} else {
				dom.addClass("active");
				displayLine(
					dom.data("startDate"),
					dom.data("endDate"),
					dom.data("step"),
					dom.data("expression"),
					dom.data("type"),
					dom.data("axis")
				);
				icon.removeClass("icon-check-empty");
				icon.addClass("icon-check");
			}
		});

		// Change date-range form
		$(".date-range-form").on("click", "button", function(e) {
			e.preventDefault();

			var form = $(this).parents("form");

			console.log(form);

			var dateToken = {
				hour: "0",
				day: "1",
				month: "1",
				year: ""
			};

			var date = "";

			if ($(this).data("range") === "week") {
				date = form.find("select option:selected").val();
			} else {
				// fetch date from token
				if (form.find("select[name=range-hour]").length > 0) {
					dateToken.hour = form.find("select[name=range-hour] option:selected").val();
				}

				if (form.find("select[name=range-day]").length > 0) {
					dateToken.day = form.find("select[name=range-day] option:selected").val();
				}

				dateToken.month = form.find("select[name=range-month] option:selected").val();
				dateToken.year = form.find("input[name=range-year]").val();

				// Build date from tokens
				date = dateToken.year + "-" + dateToken.month + "-" + dateToken.day + "T" + dateToken.hour + ":00:00";

			}

			window.location = form.attr("action") + $(this).data("range") + "/" + date;

		});

	}();

}



/**
 * Clean a worker name
 *
 * Return a worker name, stripped of all special characters
 *
 * @param	String name Worker name
 * @return String A clean worker name
 */
function cleanWorkerName(name) {
	if (typeof name === "string") {
		return name.replace(new RegExp("(\\.|:)","gm"), "");
	}
	return;

}


/**
 * Fire an effect only if effect queue is empty
 *
 * @param	JqueryObject node	jQuery node
 * @param	String		effect	effect name
 * @return void
 */
function fireEffect(node, effect) {
	if (node.queue("fx").length === 0) {
		node.effect(effect);
	}
}


/**
 * Convert a string number to an integer
 *
 * @param	String	A string
 * @return int		an Integer
 */
function parseInteger(str) {
	if (typeof str === "string") {
		return +str.replace(/,/g, "");
	}
	return;
}













// Angular.js ========================================================================================

var ResqueBoard = angular.module("ResqueBoard", []);

ResqueBoard.filter("uptime", function() {
	return function(input) {
		return moment().from(new Date(input), true);
	};
});

ResqueBoard.filter("urlencode", function() {
	return function(input) {
		return encodeURIComponent(input);
	};
});

ResqueBoard.directive("iconJob", function() {

	var data = {
		1: {
			"icon" : "job_waiting.png",
			"name" : "Pending"
		},
		2: {
			"icon" : "job_running.png",
			"name" : "Running"
		},
		3: {
			"icon" : "job_failed.png",
			"name" : "Failed"
		},
		4: {
			"icon" : "job_complete.png",
			"name" : "Completed"
		},
		63: {
			"icon" : "job_scheduled.png",
			"name" : "Scheduled"
		}
	};


	return {
		restrict: "E",
		template: "<img height=24 width=24 />",
		replace: true,
		scope: {
			status: "="
		},
		link: function (scope, element, attrs) {
			element.attr("src", "/img/" + data[scope.status].icon);
			element.attr("title", data[scope.status].name + " job");
		}
	};
});

ResqueBoard.directive("graphHorizonChart", function() {

	return {
		restrict: "E",
		template: "<div></div>",
		replace: true,
		scope: {
			workers: "=",
			length: "="
		},
		link: function (scope, element, attrs) {

			var
				context = cubism.context().size(466),
				cube = context.cube("http://"+CUBE_URL),
				horizon = context.horizon().metric(cube.metric).height(element.parent().parent().height()),
				rule = context.rule(),
				metrics = [];

			var redraw = function() {

				metrics = [];
				element.empty();

				for(var k in scope.workers) {
					metrics.push("sum(done.eq(worker, \"" + scope.workers[k].id + "\"))");
				}

				d3.select(element[0]).selectAll(".horizon")
					.data(metrics)
					.enter().append("div")
					.attr("class", "horizon")
					.call(horizon.extent([-180, 180]).title(null));

				d3.select(element[0]).append("div")
					.attr("class", "rule")
					.call(context.rule());

				d3.select(element[0]).append("div")
					.attr("class", "axis")
					.call(context.axis().orient("bottom").ticks(d3.time.minutes, 10).tickSize(6,3,0)
					.tickFormat(d3.time.format("%H:%M")));
			};

			redraw();



			scope.$watch("length", function (newVal, oldVal) {
				if (newVal !== oldVal) {
					redraw();
				}
			});

		}
	};


});

ResqueBoard.directive("graphPie", function() {

	return {
		restrict: "E",
		template: "<div></div>",
		replace: true,
		scope: {
			processedjobs: "=",
			failedjobs: "="
		},
		link: function (scope, element, attrs) {

			var datas = [{
					name : "success",
					count : Math.max(1, scope.processedjobs),
					color: "#aec7e8"
				}, {
					name : "failed",
					count : scope.failedjobs,
					color : "#e7969c"
			}];

			var m = 0;
			var z = d3.scale.category20c();
			var r = (element.parent().width()-m*2)/2;
			var arc = d3.svg.arc().innerRadius(r / 2).outerRadius(r);

			var donut = d3.layout.pie().value(function(d){
				return d.count;
			});

			var svg = d3.select(element[0])
				.append("svg:svg")
				.attr("width", (r + m) * 2)
				.attr("height", (r + m) * 2)
				.append("svg:g")
				.attr("transform", "translate(" + (r + m) + "," + (r + m) + ")")
			;

			svg.selectAll("path")
				.data(donut(datas))
				.enter().append("svg:path")
				.attr("d", arc)
				.attr("fill", function(d) { return d.data.color; })
				.attr("title", function(d){ return d.data.count + " " +	d.data.name + " jobs"; })
				.each(function(d) { this._current = d; })
			;

			var redraw = function(datas) {
				svg.selectAll("path")
					.data(donut(datas))
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
			};

			scope.$watch("processedjobs", function (newVal, oldVal) {
				if (newVal !== oldVal) {
					datas[0].count = newVal;
					redraw(datas);
				}
			});

			scope.$watch("failedjobs", function (newVal, oldVal) {
				if (newVal !== oldVal) {
					datas[1].count = newVal;
					redraw(datas);
				}
			});

		}
	};


});

var SocketListener = function($rootScope, event) {
	var socket = new WebSocket("ws://"+CUBE_URL+"/1.0/event/get");
	var callbacks = [];

	socket.onopen = function() {
		var date = new Date();
		socket.send(JSON.stringify({
			"expression": event,
			"start": date.toISOString()
		}));
	};

	var listenMessage = function() {
		socket.onmessage = function () {
			var args = arguments;
			$rootScope.$apply(function () {
				for (var i in callbacks) {
					callbacks[i].apply(socket, args);
				}
			});
		};
	};

	var registerCallback = function(callback) {
		callbacks.push(callback);
	};

	return {
		onmessage: function (callback) {
			registerCallback(callback);
			listenMessage();
		}
	};
};

ResqueBoard.factory("jobsProcessedCounter", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "got");
	return socketLister;
});

ResqueBoard.factory("jobsFailedCounter", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "fail");
	return socketLister;
});

ResqueBoard.factory("jobsSuccessCounter", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "done");
	return socketLister;
});

ResqueBoard.factory("workerStartListener", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "start");
	return socketLister;
});

ResqueBoard.factory("workerStopListener", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "shutdown");
	return socketLister;
});

ResqueBoard.factory("workerPauseListener", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "pause");
	return socketLister;
});

ResqueBoard.factory("workerResumeListener", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "resume");
	return socketLister;
});


function JobsCtrl($scope, $timeout, $http, jobsProcessedCounter, jobsFailedCounter) {
	$scope.stats = {
		"processed" : 0,
		"failed" : 0,
		"pending" : 0,
		"scheduled" : 0
	};

	jobsProcessedCounter.onmessage(function(message) {
		$scope.stats.processed++;
	});

	jobsFailedCounter.onmessage(function(message) {
		$scope.stats.failed++;
	});

	var refreshRate = 5000;

	var updateStats = function() {

		$http({method: "GET", url: "/api/stats?fields=scheduled,pending"}).
			success(function(data, status, headers, config) {
				$scope.stats.scheduled = data.scheduled.total;
				$scope.stats.pending = data.pending.total;
			}).
			error(function(data, status, headers, config) {
		});

		$timeout(updateStats, refreshRate);
	};

	$timeout(updateStats, refreshRate);
}

function QueuesCtrl($scope, jobsProcessedCounter, $http, workerStartListener, workerStopListener, $timeout) {

	$scope.stats = {totaljobs: 0};
	$scope.predicate = "stats.totaljobs";
	$scope.reverse = true;
	$scope.initFailed = false;

	var mapKeys = {};

	$http({method: "GET", url: "/api/queues?fields=totaljobs,pendingjobs,workers"}).
		success(function(data, status, headers, config) {
			$scope.queues = data;

			for (var i in $scope.queues) {
				$scope.stats.totaljobs += $scope.queues[i].stats.totaljobs;
				mapKeys[$scope.queues[i].name] = parseInt(i, 10);
			}

			updateStats();
		}).
		error(function(data, status, headers, config) {
			$scope.initFailed = true;
	});

	jobsProcessedCounter.onmessage(function(message) {
		var datas = JSON.parse(message.data);
		$scope.queues[mapKeys[datas.data.args.queue]].stats.totaljobs++;
		$scope.stats.totaljobs++;
	});

	workerStartListener.onmessage(function(message) {
		var datas = JSON.parse(message.data);
		var w = datas.data.worker.split(":");
		var queues = w[2].split(",");

		for(var q in queues) {
			if (mapKeys.hasOwnProperty(queues[q])) {
				$scope.queues[mapKeys[queues[q]]].stats.workerscount++;
			} else {
				$scope.queues.push({
					"name": queues[q],
					"stats" : {
						"totaljobs": 0,
						"pendingsjobs": 0,
						"workerscount": 1
					}
				});
				mapKeys[queues[q]] = $scope.queues.length-1;
			}
		}
	});

	var refreshRate = 5000;

	var updatePendingJobsCounter = function() {

		$http({method: "GET", url: "/api/queues?fields=pendingjobs&queues=" + Object.keys(mapKeys).join(",")}).
			success(function(data, status, headers, config) {
				for (var i in data) {
					$scope.queues[mapKeys[data[i].name]].stats.pendingjobs = parseInt(data[i].stats.pendingjobs, 10);
				}
			}).
			error(function(data, status, headers, config) {
		});

		$timeout(updatePendingJobsCounter, refreshRate);
	};

	$timeout(updatePendingJobsCounter, refreshRate);

	function updateStats() {
		for (var queue in $scope.queues) {
			$scope.queues[queue].stats.totaljobsperc =
				$scope.queues[queue].stats.totaljobs * 100 / $scope.stats.totaljobs;
		}
	}
}

function WorkersCtrl($scope, $http, jobsSuccessCounter, jobsFailedCounter,
	workerStartListener, workerStopListener, workerPauseListener, workerResumeListener) {

	$scope.initFailed = false;
	$scope.workers = {};
	$scope.length = 0;

	// Holds the temporary workers counter
	// in case jobsProcess event come before workerStart event
	var tempCounters = {};

	// Total number of jobs for all active workers
	$scope.jobsCount = 0;

	// Load initial workers datas
	$http({method: "GET", url: "/api/workers"}).
		success(function(data, status, headers, config) {

			if (!$.isEmptyObject(data)) {
				$scope.workers = data;

				var keys = Object.keys(data);
				for(var k in keys) {
					$scope.jobsCount += $scope.workers[keys[k]].stats.processed;
					$scope.updateStats(keys[k]);
					$scope.workers[keys[k]].active = true;
				}

				$scope.length = keys.length;
			}
		}).
		error(function(data, status, headers, config) {
			$scope.initFailed = true;
	});

	jobsSuccessCounter.onmessage(function(message) {
		var datas = JSON.parse(message.data);

		if ($scope.workers.hasOwnProperty(datas.data.worker)) {
			$scope.workers[datas.data.worker].stats.processed++;
			$scope.updateStats(datas.data.worker);
		} else {
			if (tempCounters.hasOwnProperty(datas.data.worker)) {
				tempCounters[datas.data.worker].stats.processed++;
			} else {
				tempCounters[datas.data.worker] = {stats: {processed: 1, failed: 0}};
			}
		}

		$scope.jobsCount++;
	});

	jobsFailedCounter.onmessage(function(message) {
		var datas = JSON.parse(message.data);
		if ($scope.workers.hasOwnProperty(datas.data.worker)) {
			$scope.workers[datas.data.worker].stats.failed++;
		} else {
			if (tempCounters.hasOwnProperty(datas.data.worker)) {
				tempCounters[datas.data.worker].stats.failed++;
			} else {
				tempCounters[datas.data.worker] = {stats: {processed: 0, failed: 1}};
			}
		}
	});

	$scope.updateStats = function(worker) {
		var start = moment($scope.workers[worker].start);
		var diff = moment().diff(start, "minutes");
		if (diff === 0) {
			$scope.workers[worker].stats.jobrate = $scope.workers[worker].stats.processed;
		} else {
			$scope.workers[worker].stats.jobrate = $scope.workers[worker].stats.processed / diff;
		}

		if ($scope.jobsCount !== 0) {
			$scope.workers[worker].stats.jobperc = $scope.workers[worker].stats.processed * 100 / $scope.jobsCount;
		}
	};

	workerStartListener.onmessage(function(message) {
		var datas = JSON.parse(message.data);
		console.log("Starting worker " + datas.data.worker);
		console.log(datas);

		var w = datas.data.worker.split(":");
		var workerId = w[0] + ":" + w[1];
		var worker = {
			"fullname": datas.data.worker,
			"id": workerId,
			"host": w[0],
			"process": w[1],
			"queues": w[2].split(","),
			"start": datas.time,
			"active": true,
			"stats": {
				"processed": 0,
				"failed": 0,
				"jobrate": 0,
				"jobperc": 0
			}
		};

		$scope.workers[workerId] = worker;
		$scope.length++;

		if (tempCounters.hasOwnProperty(workerId)) {
			$scope.workers[workerId].stats.processed += tempCounters[workerId].stats.processed;
			$scope.workers[workerId].stats.failed += tempCounters[workerId].stats.failed;
			delete tempCounters[workerId];
		}
	});

	workerStopListener.onmessage(function(message) {
		var datas = JSON.parse(message.data);
		console.log("Stopping worker " + datas.data.worker);
		delete $scope.workers[datas.data.worker];
	});

	workerPauseListener.onmessage(function(message) {
		var datas = JSON.parse(message.data);
		$scope.workers[datas.data.worker].active = false;
		console.log("Pausing worker " + datas.data.worker);
	});

	workerResumeListener.onmessage(function(message) {
		var datas = JSON.parse(message.data);
		$scope.workers[datas.data.worker].active = true;
		console.log("Resuming worker " + datas.data.worker);
	});

	$scope.pause = function(index, $event) {
		$event.preventDefault();
		$scope.workers[index].active = false;
		console.log("Pausing worker " + $scope.workers[index].id);
	};

	$scope.resume = function(index, $event) {
		$event.preventDefault();
		$scope.workers[index].active = true;
		console.log("Resuming worker " + $scope.workers[index].id);
	};

	$scope.stop = function(index, $event) {
		$event.preventDefault();
		delete $scope.workers[index];
		$scope.length--;
	};

}


ResqueBoard.controller("LatestJobsHeatmapCtrl", ["$scope", "$http", function($scope, $http) {

	$scope.jobs = [];
	$scope.loading = false;
	$scope.date = false;
	$scope.predicate = "time";

	var cal = new CalHeatMap();
	cal.init({
		id : "latest-jobs-heatmap",
		scale : [10,20,30,40],
		itemName : ["job", "jobs"],
		range: 6,
		cellsize: 10,
		browsing: true,
		browsingOptions: {
			nextLabel : "<i class=\"icon-chevron-right\"></i>",
			previousLabel : "<i class=\"icon-chevron-left\"></i>"
		},
		data: "/api/jobs/stats/{{t:start}}/{{t:end}}",
		onClick : function(start, itemNb) {
			$scope.loading = true;

			var formatDate = d3.time.format("%H:%M, %A %B %e %Y");
			$scope.date = formatDate(start);

			$http({method: "GET", url: "/api/jobs/" + (+start)/1000 + "/" + ((+start)/1000+60)}).
				success(function(data, status, headers, config) {
					$scope.jobs = [];
					for (var timestamp in data) {
						for (var job in data[timestamp]) {
							data[timestamp][job].created = new Date(data[timestamp][job].s_time*1000);
						}
						$scope.jobs = data;
					}
					$scope.loading = false;
				}).
				error(function(data, status, headers, config) {
			});
		}
	});

	$scope.clear = function() {
		$scope.date = false;
		$scope.jobs = [];
	};

}]);

function LatestJobsGraphCtrl($scope, $http) {
	$scope.jobs = [];
	listenToJobsActivities($scope, $http);

	/**
	 * Display a modal with jobs details for all
	 * jobs between a `start` and an `end` date.
	 * End time is automatically computed from the
	 * start time, and step.second
	 *
	 * @param	string startTime start time in ISO 8601 format
	 * @return void
	 */
	$scope.viewJobs = function(startTime)
	{
		var startTimeStamp = (Date.parse(startTime))/1000;

		$http({
			method: "GET",
			url: "/api/jobs/" + encodeURIComponent(startTimeStamp) + "/" + encodeURIComponent(startTimeStamp + step[0].second)
		}).
			success(function(data, status, headers, config) {
				$scope.jobs = data;
				$("#job-details").modal("show");
			}).
			error(function(data, status, headers, config) {
		});
	};
}

function ScheduledJobsCtrl($scope, $http, $timeout) {

	$scope.jobs = [];
	$scope.loading = false;
	$scope.date = false;

	$scope.stats = {
		"total" : 0,
		"future": 0,
		"past" : 0
	};

	var cal = new CalHeatMap();
	cal.init({
		id : "scheduled-jobs-graph",
		scale : [1,4,8,12],
		itemName : ["job", "jobs"],
		range: 8,
		cellsize: 10,
		browsing: true,
		browsingOptions: {
			nextLabel : "<i class=\"icon-chevron-right\"></i>",
			previousLabel : "<i class=\"icon-chevron-left\"></i>"
		},
		data: "/api/scheduled-jobs/stats/{{t:start}}/{{t:end}}",
		onClick : function(start, itemNb) {
			$scope.loading = true;
			var formatDate = d3.time.format("%H:%M, %A %B %e %Y");
			$scope.date = formatDate(start);

			$http({method: "GET", url: "/api/scheduled-jobs/" + (+start)/1000 + "/" + ((+start)/1000+60)}).
				success(function(data, status, headers, config) {
					$scope.jobs = [];
					for (var timestamp in data) {
						for (var job in data[timestamp]) {
							data[timestamp][job].created = new Date(data[timestamp][job].s_time*1000);
						}
						$scope.jobs = data;
					}
					$scope.loading = false;
				}).
				error(function(data, status, headers, config) {
			});
		}
	});

	$scope.clear = function() {
		$scope.date = false;
		$scope.jobs = [];
	};

	var refreshRate = 5000;

	var updateStats = function() {

		$http({method: "GET", url: "/api/stats?fields=scheduled_full"}).
			success(function(data, status, headers, config) {
				$scope.stats = data.scheduled;
			}).
			error(function(data, status, headers, config) {
		});

		$timeout(updateStats, refreshRate);
	};

	$timeout(updateStats, refreshRate);
}

function PendingJobsCtrl($scope, $http, $timeout) {

	$scope.stats = {
		"total" : 0
	};

	var refreshRate = 5000;

	var updateStats = function() {

		$http({method: "GET", url: "/api/stats?fields=pending_full&queues=" + Object.keys($scope.stats.queues).join(",")}).
			success(function(data, status, headers, config) {
				$scope.stats = data.pending;
			}).
			error(function(data, status, headers, config) {
		});

		$timeout(updateStats, refreshRate);
	};

	$timeout(updateStats, refreshRate);
}