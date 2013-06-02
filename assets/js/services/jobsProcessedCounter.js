angular.module("app").factory("jobsProcessedCounter", ["$rootScope", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "got");
	return socketLister;
}]);