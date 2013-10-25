angular.module("app").controller("lastestJobHeatmapController", [
	"$scope", "$http", "jobsSuccessCounter", "jobsFailedCounter", function($scope, $http, jobsSuccessCounter, jobsFailedCounter) {

	"use strict";

	$scope.jobs = [];
	$scope.loading = false;
	$scope.date = false;
	$scope.predicate = "time";

	var cal = new CalHeatMap();
	var range = 6; // Number of hours to display
	var start = new Date().setHours(new Date().getHours() - (range - 1));
	var CAL_RANGE = 1000 * 60 * 60 * range; // Number of milliseconds between the start and the end of the calendar

	cal.init({
		itemSelector : "#latest-jobs-heatmap",
		legend : [10,20,30,40],
		itemName : ["job", "jobs"],
		range: range,
		start: start,
		cellSize: 10,
		nextSelector: ".latest-jobs-graph .graph-browse-next",
		previousSelector: ".latest-jobs-graph .graph-browse-previous",
		animationDuration: 200,
		data: "api/jobs/stats/{{t:start}}/{{t:end}}",
		tooltip: true,
		onClick : function(start) {
			$scope.loading = true;

			var formatDate = d3.time.format("%H:%M, %A %B %e %Y");
			$scope.date = formatDate(start);

			$http({method: "GET", url: "api/jobs/" + (+start)/1000 + "/" + ((+start)/1000+60)}).
				success(function(data) {
					$scope.jobs = [];
					for (var timestamp in data) {
						for (var job in data[timestamp]) {
							data[timestamp][job].created = new Date(data[timestamp][job].s_time*1000);
						}
						$scope.jobs = data;
					}
					$scope.loading = false;
				}).
				error(function() {
				});
		}
	});

	start = cal.options.start; // Getting the start date of the calendar

	jobsSuccessCounter.onmessage(function(message) {
		updateHeatmap(message.data);
	});

	jobsFailedCounter.onmessage(function(message) {
		updateHeatmap(message.data);
	});

	function updateHeatmap(datas) {
		datas = JSON.parse(datas);
		var d = new Date(datas.time).getTime();
		var t = {};
		t[parseInt(d/1000, 10)] = 1;
		cal.update(t, true, cal.APPEND_ON_UPDATE);

		// Shift the calendar by one hour
		if ((d - start) >= (CAL_RANGE)) {
			cal.next();
			start =+ 1000 * 60 * 60;
		}
	}

	$scope.clear = function() {
		$scope.date = false;
		$scope.jobs = [];
	};
}]);
