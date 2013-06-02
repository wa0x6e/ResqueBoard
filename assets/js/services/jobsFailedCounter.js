angular.module("app").factory("jobsFailedCounter", ["$rootScope", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "fail");
	return socketLister;
}]);