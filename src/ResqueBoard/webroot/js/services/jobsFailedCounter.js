angular.module("app").factory("jobsFailedCounter", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "fail");
	return socketLister;
});