angular.module("app").controller("lastestJobHeatmapController", [
	"$scope", "$http", function($scope, $http) {

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
		},
		onComplete: function() {
			$(".latest-jobs-graph a").tooltip({container: "body"});
		}
	});

	$scope.clear = function() {
		$scope.date = false;
		$scope.jobs = [];
	};
}]);
