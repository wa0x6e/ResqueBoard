angular.module("app").controller("scheduledJobController", [
	"$scope", "$http", "$timeout", function($scope, $http, $timeout) {

	"use strict";

	$scope.jobs = [];
	$scope.loading = false;
	$scope.date = false;

	$scope.stats = {
		"total" : 0,
		"future": 0,
		"past" : 0
	};

	var cal = new CalHeatMap();
	var start = new Date();
	var range = 8;
	var dataString = "api/scheduled-jobs/stats/{{t:start}}/{{t:end}}";

	cal.init({
		itemSelector : "#scheduled-jobs-graph",
		legend : [1,4,8,12],
		itemName : ["job", "jobs"],
		range: range,
		start: start,
		cellSize: 10,
		animationDuration: 200,
		tooltip: true,
		nextSelector: "#scheduled-jobs-graph .graph-browse-next",
		previousSelector: "#scheduled-jobs-graph .graph-browse-previous",
		data: dataString,
		loadOnInit: false,
		onClick : function(start) {
			$scope.loading = true;
			var formatDate = d3.time.format("%H:%M, %A %B %e %Y");
			$scope.date = formatDate(start);

			$http({method: "GET", url: "api/scheduled-jobs/" + (+start)/1000 + "/" + ((+start)/1000+60)}).
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

	start = new Date(cal.getDomain(start, 1)[0]);

	$scope.clear = function() {
		$scope.date = false;
		$scope.jobs = [];
	};

	var refreshRate = 5000;

	var updateStats = function() {

		$http({method: "GET", url: "api/stats?fields=scheduled_full"}).
			success(function(data) {
				$scope.stats = data.scheduled;
			}).
			error(function() {
		});

		cal.update(dataString);

		if ((new Date() - start) > 1000 * 60 * 60 * range) {
			cal.next();
			start.setHours(start.getHours() + 1);
			start = cal.getDomain(start, 1)[0];
		}

		$timeout(updateStats, refreshRate);
	};

	updateStats();
}]);
