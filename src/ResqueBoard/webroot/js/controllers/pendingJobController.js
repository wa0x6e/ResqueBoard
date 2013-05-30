angular.module("app").controller("pendingJobController", [
	"$scope", "$timeout", "$http", function($scope, $timeout, $http) {

	$scope.stats = {
		"total" : 0,
		"queues": []
	};

	var refreshRate = 5000;

	var updateStats = function() {

		$http({method: "GET", url: "api/stats?fields=pending_full&queues=" + Object.keys($scope.stats.queues).join(",")}).
			success(function(data, status, headers, config) {
				$scope.stats = data.pending;
			}).
			error(function(data, status, headers, config) {
		});

		$timeout(updateStats, refreshRate);
	};

	$timeout(updateStats, refreshRate);
}]);
