angular.module("app").controller("scheduledJobController", [
	"$scope", "$timeout", "$http", function($scope, $timeout, $http) {

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
		data: "api/scheduled-jobs/stats/{{t:start}}/{{t:end}}",
		onClick : function(start, itemNb) {
			$scope.loading = true;
			var formatDate = d3.time.format("%H:%M, %A %B %e %Y");
			$scope.date = formatDate(start);

			$http({method: "GET", url: "api/scheduled-jobs/" + (+start)/1000 + "/" + ((+start)/1000+60)}).
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
			$("#scheduled-jobs-graph a").tooltip({container: "body"});
		}
	});

	$scope.clear = function() {
		$scope.date = false;
		$scope.jobs = [];
	};

	var refreshRate = 5000;

	var updateStats = function() {

		$http({method: "GET", url: "api/stats?fields=scheduled_full"}).
			success(function(data, status, headers, config) {
				$scope.stats = data.scheduled;
			}).
			error(function(data, status, headers, config) {
		});

		$timeout(updateStats, refreshRate);
	};

	updateStats();
}]);
