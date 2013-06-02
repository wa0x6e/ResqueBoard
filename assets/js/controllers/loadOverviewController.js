angular.module("app").controller("loadOverviewController", [
	function() {

	"use strict";

	var animationDuration = 500; // in ms

	var containerDom = $("#chart");

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
			.y0(function() { return h - margin_bottom - margin_top; })
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




}]);
