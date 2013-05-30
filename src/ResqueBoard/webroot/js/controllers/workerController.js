angular.module("app").controller("workerController", [
	"$scope", "$http", "jobsSuccessCounter", "jobsFailedCounter",
	"workersStartListener", "workersStopListener", "workersPauseListener", "workersResumeListener", function($scope, $http, jobsSuccessCounter, jobsFailedCounter,
	workersStartListener, workersStopListener, workersPauseListener, workersResumeListener) {

	$scope._init = 0;
	$scope.workers = {};
	$scope.length = 0;

	// Holds the temporary workers counter
	// in case jobsProcess event come before workerStart event
	var tempCounters = {};

	// Total number of jobs for all active workers
	$scope.jobsCount = 0;

	// Load initial workers datas
	$scope.init = function() {
		$http({method: "GET", url: "api/workers"}).
			success(function(data, status, headers, config) {

				if (!$.isEmptyObject(data)) {
					$scope.workers = data;

					var keys = Object.keys(data);
					for(var k in keys) {
						$scope.jobsCount += $scope.workers[keys[k]].stats.processed;
						$scope.updateStats(keys[k]);
						$scope.workers[keys[k]].active = true;
					}

					$scope.length = keys.length;
					$scope._init = 1;
				} else {
					$scope._init = 2;
				}


			}).
			error(function(data, status, headers, config) {
				$scope._errorCode = status;
				$scope._init = 3;
		});
	};

	$scope.init();

	jobsSuccessCounter.onmessage(function(message) {
		var datas = JSON.parse(message.data);

		if ($scope.workers.hasOwnProperty(datas.data.worker)) {
			$scope.workers[datas.data.worker].stats.processed++;
			$scope.updateStats(datas.data.worker);
		} else {
			if (tempCounters.hasOwnProperty(datas.data.worker)) {
				tempCounters[datas.data.worker].stats.processed++;
			} else {
				tempCounters[datas.data.worker] = {stats: {processed: 1, failed: 0}};
			}
		}

		$scope.jobsCount++;
	});

	jobsFailedCounter.onmessage(function(message) {
		var datas = JSON.parse(message.data);
		if ($scope.workers.hasOwnProperty(datas.data.worker)) {
			$scope.workers[datas.data.worker].stats.failed++;
		} else {
			if (tempCounters.hasOwnProperty(datas.data.worker)) {
				tempCounters[datas.data.worker].stats.failed++;
			} else {
				tempCounters[datas.data.worker] = {stats: {processed: 0, failed: 1}};
			}
		}
	});

	$scope.updateStats = function(worker) {
		var start = moment($scope.workers[worker].start);
		var diff = moment().diff(start, "minutes");
		if (diff === 0) {
			$scope.workers[worker].stats.jobrate = $scope.workers[worker].stats.processed;
		} else {
			$scope.workers[worker].stats.jobrate = $scope.workers[worker].stats.processed / diff;
		}

		if ($scope.jobsCount !== 0) {
			$scope.workers[worker].stats.jobperc = $scope.workers[worker].stats.processed * 100 / $scope.jobsCount;
		}
	};

	workersStartListener.onmessage(function(message) {
		var datas = JSON.parse(message.data);
		console.log("Starting worker " + datas.data.worker);
		console.log(datas);

		var w = datas.data.worker.split(":");
		var workerId = w[0] + ":" + w[1];
		var worker = {
			"fullname": datas.data.worker,
			"id": workerId,
			"host": w[0],
			"process": w[1],
			"queues": w[2].split(","),
			"start": datas.time,
			"active": true,
			"status": null,
			"working": false,
			"stats": {
				"processed": 0,
				"failed": 0,
				"jobrate": 0,
				"jobperc": 0
			}
		};

		$scope.workers[workerId] = worker;
		$scope.length++;
		$scope._init = 1;

		if (tempCounters.hasOwnProperty(workerId)) {
			$scope.workers[workerId].stats.processed += tempCounters[workerId].stats.processed;
			$scope.workers[workerId].stats.failed += tempCounters[workerId].stats.failed;
			delete tempCounters[workerId];
		}
	});

	workersStopListener.onmessage(function(message) {
		var datas = JSON.parse(message.data);
		console.log("Stopping worker " + datas.data.worker);
		delete $scope.workers[datas.data.worker];
		$scope.length--;

		if ($scope.length === 0) {
			$scope._init = 2;
		}
	});

	workersPauseListener.onmessage(function(message) {
		var datas = JSON.parse(message.data);
		$scope.workers[datas.data.worker].active = false;
		$scope.workers[datas.data.worker].status = "paused";
		$scope.workers[datas.data.worker].working = false;
		console.log("Pausing worker " + datas.data.worker);
	});

	workersResumeListener.onmessage(function(message) {
		var datas = JSON.parse(message.data);
		$scope.workers[datas.data.worker].status = null;
		$scope.workers[datas.data.worker].active = true;
		$scope.workers[datas.data.worker].working = false;
		console.log("Resuming worker " + datas.data.worker);
	});



	$scope.pause = function(index) {
		$http({method: "GET", url: "api/workers/pause/" + $scope.workers[index].fullname}).
			success(function(data, status, headers, config) {
				$scope.workers[index].status = "pausing …";
				$scope.workers[index].working = true;
			}).
			error(function(data, status, headers, config) {
				alert(data.message);
				if (status === 410) {
					$scope.cleanupWorker(index);
				} else if (data.message === "Worker is already paused") {
					$scope.workers[index].active = false;
					$scope.workers[index].status = "paused";
				}
		});

		console.log("Sending PAUSE command to worker " + $scope.workers[index].id);
	};

	$scope.resume = function(index) {
		$http({method: "GET", url: "api/workers/resume/" + $scope.workers[index].fullname}).
			success(function(data, status, headers, config) {
				$scope.workers[index].status = "resuming …";
				$scope.workers[index].working = true;
			}).
			error(function(data, status, headers, config) {
				alert(data.message);
				if (status === 410) {
					$scope.cleanupWorker(index);
				} else if (data.message === "Worker is already running") {
					$scope.workers[index].active = true;
					$scope.workers[index].status = null;
				}
		});
		console.log("Sending RESUME command to worker " + $scope.workers[index].id);
	};

	$scope.stop = function(index) {
		$http({method: "GET", url: "api/workers/stop/" + $scope.workers[index].fullname}).
			success(function(data, status, headers, config) {
				$scope.workers[index].status = "stopping …";
				$scope.workers[index].working = true;
			}).
			error(function(data, status, headers, config) {
				alert(data.message);
				if (status === 410) {
					$scope.cleanupWorker(index);
				}
		});
		console.log("Sending STOP command to worker " + $scope.workers[index].id);
	};

	$scope.cleanupWorker = function(index) {
		$scope.workers[index].active = true;

		delete $scope.workers[index];
		$scope.length--;

		if ($scope.length === 0) {
			$scope._init = 2;
		}
	}

}]);
