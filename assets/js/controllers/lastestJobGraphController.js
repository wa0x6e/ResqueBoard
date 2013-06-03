angular.module("app").controller("lastestJobGraphController", [
	"$scope", "$http", function($scope, $http) {

	"use strict";

	var stop = new Date(Date.now());

	$scope._init = 0;

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
	$scope.init = function($scope)
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
						.attr("x", function(d) { return x(getNextTick(d.time)); })
						.attr("clip-path", "url(#clip)")
						.transition()
						.duration(ANIMATION_DURATION)
						.attr("x", function(d) { return x(d.time) + barWidth + 2; })
					;


					rect.transition()
						.duration(ANIMATION_DURATION)
						.attr("clip-path", "url(#clip)")
						.call(barDim)
					;

					rect.exit().transition()
						.duration(ANIMATION_DURATION)
						.attr("x", function(d) { return x(d.time) + barGutter; })
						.remove()
					;

					// AXIS
					// *****
					xAxisParent.transition()
						.duration(ANIMATION_DURATION)
						.call(xAxis)
					;

					yAxisParent.transition()
						.duration(ANIMATION_DURATION)
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

				$scope._init = 1;

		}); // End d3.json
	};


	$scope.jobs = [];
	$scope.init($scope, $http);

	$scope.jobmodal = {
		_init: 0,
		lastTime: null
	};

	var modal = $("#job-details");

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
		modal.modal("show");
		$scope.jobmodal.lastTime = startTime;
		$scope.fillModal(startTime);
	};

	$scope.fillModal = function(startTime) {
		var startTimeStamp = (Date.parse(startTime))/1000;

		$http({
			method: "GET",
			url: "api/jobs/" + encodeURIComponent(startTimeStamp) + "/" + encodeURIComponent(startTimeStamp + step[0].second)
		}).
			success(function(data) {
				$scope.jobs = data;
				$scope.jobmodal._init = 1;
			}).
			error(function(data, status) {
				$scope.jobmodal._init = 3;
				$scope.jobmodal._errorCode = status;
		});
	};

	modal.on("hide", function() {
		$scope.jobs = [];
		$scope.jobmodal = {
			_init: 0,
			lastTime: null
		};
	});

}]);
