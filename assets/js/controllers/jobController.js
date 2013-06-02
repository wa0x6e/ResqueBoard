angular.module("app").controller("jobController", [
	"$scope", "$timeout", "$http", "jobsProcessedCounter", "jobsFailedCounter",
	function($scope, $timeout, $http, jobsProcessedCounter, jobsFailedCounter) {

	"use strict";

	$scope.stats = {
		"processed" : 0,
		"failed" : 0,
		"pending" : 0,
		"scheduled" : 0
	};

	jobsProcessedCounter.onmessage(function() {
		$scope.stats.processed++;
	});

	jobsFailedCounter.onmessage(function() {
		$scope.stats.failed++;
	});

	var refreshRate = 5000;

	var updateStats = function() {

		$http({method: "GET", url: "api/stats?fields=scheduled,pending"}).
			success(function(data) {
				$scope.stats.scheduled = data.scheduled.total;
				$scope.stats.pending = data.pending.total;
			}).
			error(function() {
		});

		$timeout(updateStats, refreshRate);
	};

	updateStats();
}]);
