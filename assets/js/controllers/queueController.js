angular.module("app").controller("queueController", [
	"$scope", "$http", "$timeout", "jobsProcessedCounter",  "workersStartListener", "workersStopListener",
	function($scope, $http, $timeout, jobsProcessedCounter, workersStartListener, workersStopListener) {

	"use strict";

	$scope.stats = {totaljobs: 0};
	$scope.queues = [];
	$scope.predicate = "stats.totaljobs";
	$scope.reverse = true;
	$scope._init = 0;
	$scope._errorCode = 0;

	var mapKeys = {};

	$scope.init = function() {
		$scope._init = 0;
		$http({method: "GET", url: "api/queues?fields=totaljobs,pendingjobs,workerscount"}).
			success(function(data) {
				$scope.queues = data;

				for (var i in $scope.queues) {
					$scope.stats.totaljobs += $scope.queues[i].stats.totaljobs;
					mapKeys[$scope.queues[i].name] = parseInt(i, 10);
				}

				if ($scope.queues.length === 0) {
					$scope._init = 2;
				} else {
					$scope._init = 1;
				}

				updateStats();
			}).
			error(function(data, status) {
				$scope._errorCode = status;
				$scope._errorMessage = data.message;
				$scope._init = 3;
		});
	};

	$scope.init();

	jobsProcessedCounter.onmessage(function(message) {
		var datas = JSON.parse(message.data);
		$scope.queues[mapKeys[datas.data.args.queue]].stats.totaljobs++;
		$scope.stats.totaljobs++;
	});

	workersStartListener.onmessage(function(message) {
		var datas = JSON.parse(message.data);
		var w = datas.data.worker.split(":");
		var queues = w[2].split(",");

		for(var q in queues) {
			if (mapKeys.hasOwnProperty(queues[q])) {
				$scope.queues[mapKeys[queues[q]]].stats.workerscount++;
			} else {
				$scope.queues.push({
					"name": queues[q],
					"stats" : {
						"totaljobs": 0,
						"pendingjobs": 0,
						"workerscount": 1
					}
				});
				mapKeys[queues[q]] = $scope.queues.length-1;
			}
		}

		$scope._init = 1;
	});

	var refreshFields = ["pendingjobs"];

	workersStopListener.onmessage(function(message) {
		var datas = JSON.parse(message.data);
		refreshFields.push("workerscount");
	});

	var refreshRate = 5000;

	var updatePendingJobsCounter = function() {

		$http({method: "GET", url: "api/queues?fields=" + refreshFields.join(",") + "&queues=" + Object.keys(mapKeys).join(",")}).
			success(function(data) {
				for (var i in data) {
					for (var index in refreshFields) {
						if ($scope.queues.hasOwnProperty(mapKeys[data[i].name])) {
							$scope.queues[mapKeys[data[i].name]].stats[refreshFields[index]] = parseInt(data[i].stats[refreshFields[index]], 10);
						}
					}
				}
				refreshFields = ["pendingjobs"];
			}).
			error(function() {
		});

		$timeout(updatePendingJobsCounter, refreshRate);
	};

	$timeout(updatePendingJobsCounter, refreshRate);

	function updateStats() {
		for (var queue in $scope.queues) {
			$scope.queues[queue].stats.totaljobsperc =
				$scope.queues[queue].stats.totaljobs * 100 / $scope.stats.totaljobs;
		}
	}

}]);
