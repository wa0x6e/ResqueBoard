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
	$("[data-event~=ajax-pagination]").on("change", "select", function(e){window.location=$(this).val();});

	$("#log-area").on("mouseenter", "li", function(){
		$("#log-area li[data-worker="+$(this).data("worker")+"]").addClass("hover-highlight");
	});

	$("#log-area").on("mouseleave", "li", function(){
		$("#log-area li[data-worker="+$(this).data("worker")+"]").removeClass("hover-highlight");
	});

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

	// Init syntax highlighter
	hljs.initHighlightingOnLoad();

	var QueuesList = function() {

		var queues = [];
		var init = false;

		function decrCounter(count) {
			$(".queues-count").html(parseInt($(".queues-count").html(), 10) - count);
			fireEffect($(".queues-count"), "highlight");
		}

		function incrCounter(count) {
			$(".queues-count").html(parseInt($(".queues-count").html(), 10) + count);
			fireEffect($(".queues-count"), "highlight");
		}

		return {
			init : function() {
				var tr = $(".queues-list tr");

				tr.each(function(index) {

					if ($(tr[index]).find("td").length > 0) {
						var name = $(tr[index]).find("td.queues-list-name").text();
						var count = $(tr[index]).find("td.queues-list-count").text();

						queues[name] = {
							"count" : parseInt(count, 10),
							"dom" : $(this)
						};
					}
				});

				init = true;
			},
			add : function(queueName) {
				if (init === false) {
					return false;
				}

				if (queues.hasOwnProperty(queueName) === false) {
					$(".queues-list tbody").append($("<tr><td class=\"queues-list-name\">"+queueName+"</td><td class=\"queues-list-count\">0</td></tr>"));

					queues[queueName] = {
						"count" : 0,
						"dom" : $(".queues-list tbody tr:last-child")
					};

					incrCounter(1);
				}


				queues[queueName].count++;

				queues[queueName].dom.find(".queues-list-count").html(queues[queueName].count);
				fireEffect(queues[queueName].dom, "highlight");

			},
			substract : function(queueName) {
				if (init === false) {
					return false;
				}

				if (queues.hasOwnProperty(queueName))
				{
					queues[queueName].count--;

					if (queues[queueName].count === 0) {
						queues[queueName].dom.remove();
						delete queues[queueName];
						decrCounter(1);
					} else {
						queues[queueName].dom.find(".queues-list-count").html(queues[queueName].count);
						fireEffect(queues[queueName].dom, "highlight");
					}
				}

			}
		};
	}();


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

		d3.json("http://"+serverIp+":1081/1.0/metric/get"+
			"?expression=sum(got)"+
			"&start=2012-07-07T16:00:00Z"+
			"&stop=" + formatISO(stop)+
			"&limit=" + limit +
			"&step=" + step[0].code, function(data)
			{
				if (data === null) {
					return displayCubeNoFoundError();
				}

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

				var barLabelProp = function(selection)
				{
					selection
					.attr("text-anchor", "middle")
					.attr("class", function(d){if ((height-y(d.value))<=barLabelHeight){ return "bar-label out";} return "bar-label";})
					.text(function(d){return d.value;})
					.call(barLabelDim)
					;
				};

				var barLabelDim = function(selection)
				{
					selection
					.attr("y", function(d) { return ((height-y(d.value))>barLabelHeight ? y(d.value) : (y(d.value) - barLabelHeight)); })
					.attr("dy", "1.5em")
					.attr("x", function(d) { return x(d.time) + barWidth/2; })
					;
				};

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
						.call(xAxis)
					;

					yAxisParent.transition()
						.duration(duration)
						.call(yAxis)
					;

				}

				var metricSocket = new WebSocket("ws://"+serverIp+":1081/1.0/metric/get");

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
	 * Display a modal with jobs details for all
	 * jobs between a `start` and an `end` date.
	 * End time is automatically computed from the
	 * start time, and step.second
	 *
	 * @param	string startTime start time in ISO 8601 format
	 * @return void
	 */
	function displayJobsModal(startTime)
	{
		var startTimeStamp = (Date.parse(startTime))/1000;
		var modalTimestamp = $("#job-details").data("timestamp");

		if (modalTimestamp !== startTimeStamp)
		{
			$.ajax({
				url : "/api/jobs/" + startTimeStamp + "/" + (startTimeStamp + step[0].second),
				success : function(message){
					$("#job-details .modal-body").html(
						$("#jobs-tpl").render(message)
					);
					$("#job-details").data("timestamp", startTimeStamp);
					$("#job-details .modal-header .badge").html(message.length);

					$("#job-details").modal("show");
				}
			});
		}
		else
		{
			$("#job-details-modal").modal("show"); // Repeat because ajax is asynchronous
		}
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
				relativeTime: moment(data.time).fromNow(),
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

	var Job = function()
	{
		var totalJobs = 0;
		var jobsChartInit = 0;
		var jobsStats = {};
		var jobChartType = "";

		return {
			initJobsChart : function(chartType) {
				if ($(".worker-stats").length !== 0)
				{
					var getStaticStats = function(id)
						{
							if ($("#" + id).length === 0)
							{
								return false;
							}

							var $this = $("#" + id);
							var processedJobsCountDOM = $this.find("[data-status=processed]");
							var failedJobsCountDOM = $this.find("[data-status=failed]");
							return {
								processedJobsCountDOM : processedJobsCountDOM,
								failedJobsCountDOM : failedJobsCountDOM,
								processedJobsCount : parseInteger(processedJobsCountDOM.html()),
								failedJobsCount : parseInteger(failedJobsCountDOM.html()),
								chart: $this.find("[data-type=chart]")
							};
						};

					$(".worker-stats").each(function(data){
						var $this = $(this);
						var processedJobsCountDOM = $this.find("[data-status=processed]");
						var failedJobsCountDOM = $this.find("[data-status=failed]");
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

					jobsStats["global-worker-stats"] = getStaticStats("global-worker-stats");
					jobsStats["active-worker-stats"] = getStaticStats("active-worker-stats");

					jobsChartInit = 1;

					if (chartType === "pie")
					{
						jobPieChart.init(jobsStats);
						jobChartType = chartType;
					}
				}
				else {
					jobsChartInit = 2;
				}
			},
			/**
			 * Add a new workers and its stats
			 * @param String workerId Worker classname
			 */
			addJobChart : function(workerId) {
				var $this = $("#" + workerId);
				var processedJobsCountDOM = $this.find("[data-status=processed]");
				var failedJobsCountDOM = $this.find("[data-status=failed]");
				var processedJobsCount = parseInteger(processedJobsCountDOM.html());
				var chartDOM = $this.find("[data-type=chart]");

				jobsStats[workerId] = {
					processedJobsCountDOM: processedJobsCountDOM,
					processedJobsCount: processedJobsCount,
					failedJobsCountDOM: failedJobsCountDOM,
					failedJobsCount : parseInteger(failedJobsCountDOM.html()),
					chart : chartDOM,
					chartType : chartDOM.data("chart-type")
				};

				if (jobChartType === "pie") {
					jobPieChart.add(jobsStats[workerId]);
				}
			},
			/**
			 * Remove a worker from the set
			 *
			 * @param	String	workerId	Worker ID classname
			 * @return void
			 */
			removeJobChart : function(workerId) {
				delete jobsStats[workerId];
			},
			updateJobsChart : function(workerId, level) {
				if (jobsChartInit === 2) {
					return;
				}

				jobsStats[workerId].processedJobsCount++;
				totalJobs++;

				if (level === 400)
				{
					jobsStats[workerId].failedJobsCount++;
				}

				var updateCounter = function(workerId, success)
				{
					var index = "processedJobsCount";
					if (!success)
					{
						index = "failedJobsCount";
					}

					jobsStats[workerId][index + "DOM"].html(number_format(jobsStats[workerId][index]));
					fireEffect(jobsStats[workerId][index + "DOM"], "highlight");
				};


				// Refresh Counter
				if (level === 400)
				{
					updateCounter(workerId, false);
				}

				updateCounter(workerId, true);

				if (jobsStats["active-worker-stats"] !== false)
				{
					jobsStats["active-worker-stats"].processedJobsCount++;
					updateCounter("active-worker-stats", true);

					if (level === 400)
					{
						jobsStats["active-worker-stats"].failedJobsCount++;
						updateCounter("active-worker-stats", false);
					}

				}
				if (jobsStats["global-worker-stats"] !== false)
				{
					jobsStats["global-worker-stats"].processedJobsCount++;
					updateCounter("global-worker-stats", true);

					if (level === 400)
					{
						jobsStats["global-worker-stats"].failedJobsCount++;
						updateCounter("global-worker-stats", false);
					}
				}

				// Refresh Chart
				switch (jobsStats[workerId].chartType)
				{
					case "pie" :
						jobPieChart.redraw(jobsStats[workerId], true);
						jobPieChart.redraw(jobsStats["active-worker-stats"], false);
						jobPieChart.redraw(jobsStats["global-worker-stats"], false);
						break;
					case "horizontal-bar" :
						for (var i = 0, length = jobsStats.length; i < length; i++) {
							if (jobsStats[i] !== false) {
								jobsStats[i].chart.animate({
									width: Math.floor((jobsStats[i].processedJobsCount / totalJobs) * 100) + "%"
								}, 500);
							}
						}
				}

			},
			isInit : function() {
				return jobsChartInit !== 0;
			}
		};
	}();


	/**
	 * Listen to workers activities in realtime
	 * and update related counters
	 *
	 * @return void
	 */
	function listenToWorkersJob(chartType, layout) {

		var eventProcessor = function(){
			var getWorkerId = function(message) {
				return message.data.worker;
			};

			return {
				processDone : function(message){
					Job.updateJobsChart(
						cleanWorkerName(getWorkerId(message)),
						message.data.level
					);
				},
				processGot : function(message){
					Job.updateJobsChart(
						cleanWorkerName(getWorkerId(message)),
						message.data.level
					);
				},
				processFail : function(message){
					Job.updateJobsChart(
						cleanWorkerName(getWorkerId(message)),
						message.data.level
					);
				},
				processStop : function(message){
					stopWorkerEvent(cleanWorkerName(getWorkerId(message)));
				},
				processStart : function(message){
					startWorkerEvent(message.data.worker, layout);
				}
			};
		}();


		// Start Listening to events
		// *************************
		var events = {
			//got	: {expression: "got", format: function(data){return "job #" + data.job_id;}},
			//fork	: {expression: "fork", format: function(data){return "job #" + data.job_id;}},
			done	: {expression: "done", format: function(data){return "job #" + data.job_id;}},
			fail	: {expression: "fail", format: function(data){return "job #" + data.job_id;}},
			stop	: {expression: "shutdown", format: function(data){return "worker #" + data.worker;}},
			start : {expression: "start", format: function(data){return "worker #" + data.worker;}}
		};

		for(var e in events) {
			init(e);
		}

		Job.initJobsChart(chartType);

		function init(e) {
			var socket = new WebSocket("ws://"+serverIp+":1081/1.0/event/get");
			socket.onopen = function() {
				socket.send(JSON.stringify({
					"expression": events[e].expression,
					"start": formatISO(stop)
				}));
			};

			socket.onmessage = function(message) {
				process(e, JSON.parse(message.data));
			};
		}

		// Process Messages
		// *************************

		function process(type, data)
		{
			switch(type) {
				case "done" :
					eventProcessor.processDone(data);
					break;
				case "fail" :
					eventProcessor.processFail(data);
					break;
				case "stop" :
					eventProcessor.processStop(data);
					break;
				case "start" :
					eventProcessor.processStart(data);
					break;
				//case "done" :
				//	eventProcessor.processDone(data);
			}
		}
	}


	var jobPieChart = function()
	{
		var initData = function(d){

			var successCount = d.processedJobsCount - d.failedJobsCount;

			var datas = [{
					name : "success",
					count : (successCount === 0 && d.failedJobsCount === 0) ? 1 : successCount,
					color: "#aec7e8"
				}, {
					name : "failed",
					count : d.failedJobsCount,
					color : "#e7969c"
				}];
			return datas;
		};

		var m = 0;
		var z = d3.scale.category20c();

		var donut = d3.layout.pie().value(function(d){
						return d.count;
					});



		return {
			init : function(jobStats)
			{
				for(var i in jobStats)
				{
					if (jobStats.hasOwnProperty(i))
					{
						var data = initData(jobStats[i]);
						var parent = jobStats[i].chart;

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
							.attr("title", function(d){return d.data.count + " " +	d.data.name + " jobs"; })
							.each(function(d) { this._current = d; })
						;
					}
				}
			},

			add : function(stats) {
				var data = initData(stats);
				var parent = stats.chart;

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
					.attr("title", function(d){return d.data.count + " " +	d.data.name + " jobs"; })
					.each(function(d) { this._current = d; })
				;
			},

			redraw : function(stat)
			{
				var data = initData(stat);

				var parent = stat.chart;
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


	var WorkerActivities = function() {

		var init = false;

		var
			context = cubism.context().size(466),
			cube = context.cube("http://"+serverIp+":1081"),
			horizon = context.horizon().metric(cube.metric).height(53),
			rule = context.rule();

		return {
			isInit : function() {
				return init;
			},
			init : function() {
				WorkerActivities.redraw();
				init = true;
			},
			redraw : function(initContainer) {

				if (initContainer) {
					$("#working-area tbody tr:first-child td:last-child").append($("<div class=\"padd-fixer\"><div id=\"worker-activities\"></div></div>"));
				} else {
					$("#worker-activities").empty();
				}


				var workersIds = [];
				var metrics = [];

				$(".worker-stats h4").each(function(i){ workersIds.push($(this).text());});

				if (workersIds.length === 0) {
					context.stop();
					return;
				}


				for (var i = 0, length = workersIds.length; i < length; i++) {
					metrics.push("sum(done.eq(worker, \"" + workersIds[i] + "\"))");
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
		};

	}();

	/**
	 * Draw a horizon chart of workers activities
	 * @return void
	 */
	function listenToWorkersActivities()
	{
		WorkerActivities.init();
	}


	/**
	 * Create a pie chart from a set of data
	 *
	 * @param	{[type]} id		[description]
	 * @param	{[type]} total	[description]
	 * @param	{[type]} data	[description]
	 * @param	{[type]} average [description]
	 */
	function pieChart(id, total, data, average)
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


	function jobsLoad()
	{
		var
			context = cubism.context().size($("#jobs-load").width()),
			cube = context.cube("http://"+serverIp+":1081"),
			horizon = context.horizon().metric(cube.metric).height(100),
			rule = context.rule();

		var workersIds = [];
		var metrics = [];


		metrics.push("sum(got)");


		d3.select("#jobs-load").append("div")
		.attr("class", "rule")
		.call(context.rule());

		d3.select("#jobs-load").selectAll(".horizon")
		.data(metrics)
		.enter().append("div")
		.attr("class", "horizon")
		.call(horizon.extent([-180, 180]).title(null));

		d3.select("#jobs-load").append("div")
		.attr("class", "axis")
		.call(context.axis().orient("bottom").ticks(d3.time.minutes, 10).tickSize(6,3,0)
		.tickFormat(d3.time.format("%H:%M")));
	}

	/**
	 * Create a line graph of monthly jobs load
	 *
	 * @param	int average	Total average number of jobs by day
	 * @return void
	 */
	function monthlyJobsLoad(average)
	{
		var loadChart = function(start, end, title)
		{
			d3.json("http://"+serverIp+":1081/1.0/metric/get"+
				"?expression=sum(got)"+
				"&start="+ start +
				"&stop=" + end +
				"&step=" + step[4].code,
				function(data)
				{
					if (data === null) {
						return displayCubeNoFoundError();
					}

					data = data.map(function(d){return {time:new Date(d.time), value:	d.value};});

					var w = $("#jobs-load-monthly").width();
					var h = $("#jobs-load-monthly").height();
					var margin_top = 20;
					var margin_right = 5;
					var margin_bottom = 35;
					var margin_left = 45;

					var xScale = d3.time.scale()
					.domain([data[0].time, data[data.length-1].time])
					.range([0, w - margin_left - margin_right]);

					var yScale = d3.scale.linear()
					.domain([0, Math.max(d3.max(data.map(function(d){return d.value;})), average)*1.25])
					.range([h - margin_top - margin_bottom, 0]);

					d3.select("#jobs-load-monthly").selectAll("svg").remove();

					var svg = d3.select("#jobs-load-monthly").append("svg")
						.attr("width", w)
						.attr("height", h)
					;

					var line = d3.svg.line()
						.x(function(d) {return xScale(d.time);})
						.y(function(d) {return yScale(d.value);})
					;

					var garea = d3.svg.area()
						.x(function(d) {return xScale(d.time);})
						.y0(h - margin_bottom - margin_top)
						.y1(function(d) {return yScale(d.value);})
					;

					var xAxis = d3.svg.axis()
						.scale(xScale)
						.tickFormat(d3.time.format("%d"))
						.tickSubdivide(1)
						.orient("bottom")
					;

					var yAxis = d3.svg.axis()
						.scale(yScale)
						.tickSize(-w + margin_left + margin_right)
						.ticks(8)
						.orient("left")
					;

					svg.append("g")
						.attr("class", "x-axis")
						.attr("transform", "translate(" + margin_left + "," + (h - margin_bottom) + ")")
						.call(xAxis)
					;

					svg.append("g")
						.attr("class", "y-axis")
						.attr("transform", "translate(" + (margin_left-1) + "," + margin_top + ")")
						.call(yAxis)
					;

					var graph_group = svg.append("g")
						.attr("width", w - margin_left + margin_right)
						.attr("height", h - margin_top + margin_bottom)
						.attr("transform", "translate(" + margin_left + "," + margin_top + ")")
					;

					graph_group.append("rect")
						.attr("x", 0)
						.attr("y", yScale(average))
						.attr("width", w - margin_left - margin_right)
						.attr("height", h - yScale(average) - margin_bottom - margin_top)
						.attr("class", "graph-average-area")
					;

					graph_group.append("path")
						.attr("class", "graph-line")
						.attr("d", line(data))
					;

					graph_group.append("path")
						.attr("class", "graph-area")
						.attr("d", garea(data))
					;



					svg.append("text")
						.attr("x", w/2)
						.attr("y", h - 3)
						.attr("text-anchor", "middle")
						.text(title)
						.attr("class", "graph-title")
					;

					svg.append("text")
						.attr("x", margin_left/2)
						.attr("y", 10)
						.attr("text-anchor", "left")
						.text("Jobs/days")
						.attr("class", "graph-legend")
					;

					svg.append("text")
						.attr("x", w - margin_right)
						.attr("y", 10)
						.attr("text-anchor", "end")
						.text("Average : " + number_format(average) + " jobs/day")
						.attr("class", "graph-legend")
					;
				}
			);
		}; // end load chart

		var initChart = function()
		{
			var selection = $("#jobs-load-monthly-selector option:selected");
			var startTime = moment(selection.val() + "-01", "YYYY-MM-DD");
			var stopTime = moment(startTime).add("M", 1);

			loadChart(startTime.format("YYYY-MM-DD"), stopTime.format("YYYY-MM-DD"), selection.text());
		};

		initChart();

		$("#jobs-load-monthly-selector").on("change", function(e)
		{
			initChart();
		});
	}

$(".workers-list, #working-area").on("click", ".stop-worker", function(event){
	event.preventDefault();

	var workerId = $(this).data("workerId");
	var workerName = $(this).data("workerName");

	$.ajax({
		url: "/api/workers/stop/" + workerId,
		statusCode: {
			404: function() {
				alert("page not found");
			}
		}
	}).done(function(data){
		if (data.status === true) {
			stopWorkerEvent(workerName);
		} else {
			if (data.message) {
				alert(data.message);
			} else {
				alert("An unknown error has occured while stopping the worker");
			}

		}

	});

});



$(".get-worker-info").on("click", function(event){
	event.preventDefault();

	var workerId = $(this).data("workerId");

	$.ajax({
		url: "/api/workers/getinfo/" + workerId,
		statusCode: {
			404: function() {
				alert("page not found");
			}
		}
	}).done(function(data){

		$("#worker-details .modal-body").html(
			$("#workers-tpl").render(data)
		);

		$("#worker-details").modal("show");
	});

});


$(".start-worker").on("click", function(event){
	event.preventDefault();

	$.ajax({
		url: "/api/workers/start"
	}).done(function(data){
		$("#worker-form").html(data);
		$("#worker-form").modal("show");
		$(".pop-over").popover({trigger: "hover"});
	});

});

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

			d3.json("http://"+serverIp+":1081/1.0/metric/get"+
			"?expression=sum(got)" +
			"&start="+ startDate +
			"&stop=" + endDate +
			"&step=" + dataStep, function(data)
			{

				placeholder.remove();

				if (data === null) {
					return displayCubeNoFoundError();
				}

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

			d3.json("http://"+serverIp+":1081/1.0/metric/get"+
			"?expression=" + expression +
			"&start="+ start +
			"&stop=" + end +
			"&step=" + dataStep, function(data)
			{
				if (data === null) {
					return displayCubeNoFoundError();
				}

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
 * Scheduler Worker Page
 * Future scheduled jobs calendar graph
 * @since 1.5.0
 */
if ($("#scheduled-jobs-graph").length > 0) {
	CalHeatMap.init({
		id : "scheduled-jobs-graph",
		scales : [10,20,30,40],
		itemName : ["job", "jobs"],
		onClick : function(start, end, itemNb) {

			var formatDate = d3.time.format("%H:%M, %A %B %e %Y");

			$("#scheduled-jobs-list").html("<h2>Jobs scheduled for " + formatDate(start) + "</h2>");
			$("#scheduled-jobs-list").append("<div class=\"alert alert-info\" id=\"scheduled-jobs-loading\">Loading datas ...</div>");

			d3.json("/api/scheduled-jobs/" + start.getTime()/1000 + "/" + end.getTime()/1000, function(data) {

				$("#scheduled-jobs-loading").remove();
				$("#scheduled-jobs-list").append("<ul class=\"unstyled\" id=\"job-details\"></ul>");

				for (var timestamp in data) {

					for (var job in data[timestamp]) {
						data[timestamp][job].created = new Date(data[timestamp][job].s_time*1000);
						data[timestamp][job].args = print_r(data[timestamp][job].args);
					}

					$("#scheduled-jobs-list ul")
					.append(
						$("#scheduled-jobs-list-tpl").render(data[timestamp])
					);
				}

				var jobsCount = $("#scheduled-jobs-list ul li").length;

				if (jobsCount === 0) {
					$("#scheduled-jobs-list").append("<div class=\"alert\">No jobs found for this period</div>");
				} else {
					$("#scheduled-jobs-list h2").prepend("<span class=\"badge pull-right\">" + jobsCount + "</span>");
				}

			});

		}
	});
}


/**
 * Processed after stopping a worker
 *
 * Handle all the DOM manipulation to remove a worker from the page
 * and refresh all the related counters
 *
 * @param	String workerName Clean worker name (processed with cleanWorkerName())
 * @return void
 */
function stopWorkerEvent(workerName) {
	var workerDom = $("#" + cleanWorkerName(workerName));
	if (workerDom.length === 1) {
		workerDom.fadeOut(400, function(){

			workerDom.remove();

			if ($(".workers-count").length > 0) {
				var counter = $(".workers-count");
				var count = counter.html();
				counter.html(parseInt(count, 10) - 1);
				fireEffect(counter, "highlight");
			}

			var workerQueues = workerDom.find(".queue-name");
			workerQueues.each(function(i) {
				QueuesList.substract($(workerQueues[i]).text());
			});

			if (Job.isInit()) {
				Job.removeJobChart(cleanWorkerName(workerName));
			}

			if (WorkerActivities.isInit()) {
				WorkerActivities.redraw(workerDom.find("#worker-activities").length === 1);
			}

		});
	}



}


/**
 * Processed after starting a worker
 *
 * Do all the works to add worker details in the DOM
 *
 * @param	String workerName Worker ID
 * @return void
 */
function startWorkerEvent(workerId, layout) {
	$.ajax({
		url: "/render/worker/" + layout + "/" + workerId,
		statusCode: {
			404: function() {
				alert("page not found");
			}
		}
	}).done(function(data){
		switch(layout) {
			case "list" :
				$(".workers-list").append(data);
				break;
			case "table" :
				$("#working-area tbody").append(data);
				break;
		}

		if ($(".workers-count").length > 0) {
			var count = $(".workers-count").html();
			$(".workers-count").html(parseInt(count, 10) + 1);
			fireEffect($(".workers-count"), "highlight");
		}

		var workerQueues = $("#" + cleanWorkerName(workerId)).find(".queue-name");
		workerQueues.each(function(i) {
			QueuesList.add($(workerQueues[i]).text());
		});

		if (Job.isInit()) {
			Job.addJobChart(cleanWorkerName(workerId));
		}

		if (WorkerActivities.isInit()) {
			WorkerActivities.redraw();
		}
	});
}

/**
 * Use a form select's options as navigation
 */
$(".navigator").on("change", "select", function () {
	window.location = $("option", this).filter(":selected").val();
});


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


/**
 *
 * @param	int x A number
 * @return	string
 */
function number_format(x)
{
	if (typeof x === "integer") {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}
	return;
}

function displayCubeNoFoundError()
{
	var alert = $("<div class=\"alert alert-error page-alert\"><h4>Error</h4>Unable to connect to Cube server</div>").hide();
	$("#main").prepend(alert);
	alert.slideDown("slow").delay(2000).slideUp("slow", function(){alert.remove();});
}

/**
* PHP-like print_r() & var_dump() equivalent for JavaScript Object
*
* @author Faisalman <movedpixel@gmail.com>
* @license http://www.opensource.org/licenses/mit-license.php
* @link http://gist.github.com/879208
*/
var print_r = function(obj,t){

// define tab spacing
var tab = t || '';
// check if it's array
var isArr = Object.prototype.toString.call(obj) === '[object Array]' ? true : false;
// use {} for object, [] for array
var str = isArr ? ('Array\n' + tab + '[\n') : ('Object\n' + tab + '{\n');

// walk through it's properties
for(var prop in obj){
if (obj.hasOwnProperty(prop)) {
var val1 = obj[prop];
var val2 = '';
var type = Object.prototype.toString.call(val1);
switch(type){
// recursive if object/array
case '[object Array]':
case '[object Object]':
val2 = print_r(val1, (tab + '\t'));
break;
case '[object String]':
val2 = '\'' + val1 + '\'';
break;
default:
val2 = val1;
}
str += tab + '\t' + prop + ' => ' + val2 + ',\n';
}
}
// remove extra comma for last property
str = str.substring(0, str.length-2) + '\n' + tab;
return isArr ? (str + ']') : (str + '}');
};
