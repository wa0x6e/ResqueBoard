angular.module("app").controller("logActivityController", [

	function() {

	"use strict";

	var stop = new Date(Date.now());

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



	$("#clear-log-area").on("click", function(){
		$("#log-area").children().remove();
		resetCounters();
	});

	$("#log-filter-form").on("change", "input", function(){

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


	$("#log-sweeper-form").on("click", "button[data-rel=verbosity]", function(){
			var toRemove = $("#log-area").children("li[data-verbosity="+$(this).data("level")+"]");
			updateCounters(toRemove);
			toRemove.remove();

		return false;
	});

	$("#log-sweeper-form").on("click", "button[data-rel=type]", function(){
		var toRemove = $("#log-area").children("li[data-type="+$(this).data("type")+"]");
		updateCounters(toRemove);
		toRemove.remove();

		return false;
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


}]);
