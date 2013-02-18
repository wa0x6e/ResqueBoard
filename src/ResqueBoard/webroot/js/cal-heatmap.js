var CalHeatMap = (function() {

	var graph = {};

	var options = {
		// DOM ID of the container to append the graph to
		id : "scheduled-jobs-graph",

		// Threshold for each scale
		scales : [100,400,500,600],

		// Number of hours to display on the graph
		range : 12,

		// Size of each cell, in pixel
		cellsize : 10,

		// Padding between each cell, in pixel
		cellpadding : 2,

		dateFormat : "%H:%M, %A %B %e %Y",

		// Callback when clicking on a time block
		onClick : function(start, end, itemNb) {},

		// Whether to display the scale
		displayScale : true,


		itemName : ["job", "jobs"],

		// Start of the graph
		start : new Date()
	};

	var
		graphStartDate = getStartHour(options.start),
		dataEndDate = new Date(graphStartDate.getTime() + 3600*(options.range)*1000),
		graphEndDate = dataEndDate;

	var w, h, m = [0, 0, 25, 0]; // top right bottom left margin

	var formatDate = d3.time.format(options.dateFormat),
		formatNumber = d3.format(",d");

	var svg = null;
	var rect = null;

	var _onClick = function(d, itemNb) {
		options.onClick(
			new Date(d.getFullYear(), d.getMonth(), d.getDate(), d.getHours(), d.getMinutes(), 0),
			new Date(d.getFullYear(), d.getMonth(), d.getDate(), d.getHours(), d.getMinutes(), 59),
			itemNb
		);
	};




	/**
	 * Display the graph for the first time
	 */
	function init() {

		var graphLegendHeight = options.cellsize*2;

		w = options.cellsize*6 + options.cellpadding*6 + options.cellpadding;
		h = options.cellsize*10 + options.cellpadding*9 + options.cellpadding;

		// Get datas from remote, parse them to expected format, then display them in the graph
		d3.json("/api/scheduled-jobs/stats/"+ parseInt(options.start.getTime()/1000, 10) + "/" + parseInt(dataEndDate.getTime()/1000, 10), function(data) {
			display(parseDatas(data));
		});

		svg = d3.select("#" + options.id + " .graph")
			.selectAll()
			.data(d3.time.hours(graphStartDate, graphEndDate).map(function(d) {
				return d.getTime()/1000;
			}))
			.enter().append("div")
			.attr("class", "hour")
			.style("width", w + "px")
			.style("height", h + graphLegendHeight + "px")
			.style("display", "inline-block")
			.append("svg:svg")
			.attr("width", w)
			.attr("height", h + graphLegendHeight)
			.append("svg:g")
			.attr("transform", "translate(0, 1)");

		svg.append("svg:text")
			.attr("y", h + graphLegendHeight/1.5)
			.attr("class", "graph-label")
			.attr("text-anchor", "middle")
			.attr("vertical-align", "middle")
			.attr("x", w/2)
			.text(function(d) { var date = new Date(d*1000); return date.getHours() + ":00"; });

		rect = svg.selectAll("rect")
			.data(function(d) { return d3.time.minutes(getStartHour(d*1000), getStartHour((d+3600)*1000)); })
			.enter().append("svg:rect")
			.attr("class", "graph-rect")
			.attr("width", options.cellsize)
			.attr("height", options.cellsize)
			.attr("x", function(d) { var p = Math.floor(d.getMinutes()/10); return p * options.cellsize + p * options.cellpadding; })
			.attr("y", function(d) { var p = d.getMinutes() % 10; return p * options.cellsize + p * options.cellpadding; });

		rect.append("svg:title");

		if (options.displayScale) {
			displayScale();
		}
	}

	function displayScale() {

		var scaleIndex = d3.range(0, options.scales.length);

		var scale = d3.select("#" + options.id).append("svg:svg")
		.attr("class", "graph-scale")
		.attr("height", options.cellsize + (options.cellpadding*2))
		;

		scale.selectAll().data(scaleIndex).enter()
		.append("svg:rect")
		.attr("width", options.cellsize)
		.attr("height", options.cellsize)
		.attr("class", function(d){ return "graph-rect q" + (d+1); })
		.attr("transform", function(d) { return "translate(" + (d * (options.cellsize + options.cellpadding))  + ", " + options.cellpadding + ")"; })
		.append("svg:title")
		.text(function(d) {
			var nextThreshold = options.scales[d+1];
			if (d === 0) {
				if (0 === nextThreshold) {
					return "0 " + options.itemName[0];
				}
				return "between 0 and " + (nextThreshold-1) + " " + options.itemName[1];
			} else if (d === options.scales.length-1) {
				return "more than " + options.scales[d] + " " + options.itemName[1];
			} else {
				if (options.scales[d] === nextThreshold) {
					return options.scales[d] + " " + (options.itemName[options.scales[d] > 1 ? 1 : 0]);
				}
				return "between " + options.scales[d] + " and " + (nextThreshold-1) + " " + options.itemName[1];
			}
		})
		;
	}


	/**
	 * Colorize all rectangles according to their items count
	 *
	 * @param  {[type]} data  [description]
	 */
	function display (data) {
		svg.each(function(hour) {
			d3.select(this).selectAll("rect")
				.attr("class", function(d) {
					var min = d.getMinutes();

					if (d < options.start) {
						return "graph-rect q0";
					}

					return "graph-rect" +
					((data.hasOwnProperty(hour) && data[hour].hasOwnProperty(min)) ?
						(" " + scale(data[hour][min])) : ""
					);
				})
				.on("click", function(d) {
					var min = d.getMinutes();
					return _onClick(
						d,
						(data.hasOwnProperty(hour) && data[hour].hasOwnProperty(min)) ? data[hour][min] : 0
					);
				})
				.select("title")
				.text(function(d) {

					if (d < options.start) {
						return "";
					}

					var min = d.getMinutes();
					return (
					((data.hasOwnProperty(hour) && data[hour].hasOwnProperty(min)) ?
						(formatNumber(data[hour][min]) + " " + options.itemName[data[hour][min] > 1 ? 1 : 0] + " at ") :
						""
						) + formatDate(d));
				});
			}
		);
	}


	/**
	 * Convert a JSON result into the expected format
	 *
	 * @param  {[type]} data [description]
	 * @return {[type]}      [description]
	 */
	function parseDatas(data) {
		var stats = {};

		for (var d in data) {
			var date = new Date(d*1000);
			var hourStartTimestamp = new Date(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours());
			hourStartTimestamp = hourStartTimestamp.getTime()/1000;

			var min = date.getMinutes();
			if (typeof stats[hourStartTimestamp] === "undefined") {
				stats[hourStartTimestamp] = {};
			}

			if (typeof stats[hourStartTimestamp][min] !== "undefined") {
				stats[hourStartTimestamp][min] += data[d];
			} else {
				stats[hourStartTimestamp][min] = data[d];
			}
		}

		return stats;
	}

	/**
	 * Return the classname for the specified value, on the scale
	 *
	 * @param  Item count n Number of items for that perdiod of time
	 * @return string		Classname according to the scale
	 */
	function scale(n) {
		for (var i = 0, total = options.scales.length-1; i < total; i++) {
			if (n <= options.scales[i]) {
				return "q" + (i+1);
			}
		}
		return n === 0 ? "" : "q" + options.scales.length;
	}


	/**
	 * Return the start of an hour
	 * @param  number|Date	d	A date, or timestamp in milliseconds
	 * @return Date				The start of the hour
	 */
	function getStartHour(d) {
		if (typeof d === "number") {
			d = new Date(d);
		}
		return new Date(d.getFullYear(), d.getMonth(), d.getDate(), d.getHours(), 0);
	}

	graph.init = function(settings) {

		// Merge settings with default
		if ( settings !== null && settings !== undefined && settings !== "undefined" ){
				for ( var opt in options ) {
					if ( settings[ opt ] !== null &&
						settings[ opt ] !== undefined &&
						settings[ opt ] !== "undefined" ){
							options[ opt ] = settings[ opt ];
				}
			}
		}

		init();
	};

	return graph;

})(CalHeatMap);