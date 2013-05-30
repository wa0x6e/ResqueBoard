angular.module("app").controller("jobController", [
	"$scope", "$timeout", "$http", "jobsProcessedCounter", "jobsFailedCounter",
	function($scope, $timeout, $http, jobsProcessedCounter, jobsFailedCounter) {

	$scope.stats = {
		"processed" : 0,
		"failed" : 0,
		"pending" : 0,
		"scheduled" : 0
	};

	jobsProcessedCounter.onmessage(function(message) {
		$scope.stats.processed++;
	});

	jobsFailedCounter.onmessage(function(message) {
		$scope.stats.failed++;
	});

	var refreshRate = 5000;

	var updateStats = function() {

		$http({method: "GET", url: "api/stats?fields=scheduled,pending"}).
			success(function(data, status, headers, config) {
				$scope.stats.scheduled = data.scheduled.total;
				$scope.stats.pending = data.pending.total;
			}).
			error(function(data, status, headers, config) {
		});

		$timeout(updateStats, refreshRate);
	};

	updateStats();
}]);
