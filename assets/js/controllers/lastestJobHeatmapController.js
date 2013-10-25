angular.module("app").controller("lastestJobHeatmapController", [
	"$scope", "$http", function($scope, $http) {

	"use strict";

	$scope.jobs = [];
	$scope.loading = false;
	$scope.date = false;
	$scope.predicate = "time";

	var cal = new CalHeatMap();
	var range = 6;
	cal.init({
		itemSelector : "#latest-jobs-heatmap",
		scale : [10,20,30,40],
		itemName : ["job", "jobs"],
		range: range,
		start: new Date().setHours(new Date().getHours() - (range - 1)),
		cellSize: 10,
		nextSelector: ".latest-jobs-graph .graph-browse-next",
		previousSelector: ".latest-jobs-graph .graph-browse-previous",
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

	$scope.clear = function() {
		$scope.date = false;
		$scope.jobs = [];
	};
}]);
