angular.module("app").factory("jobsProcessedCounter", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "got");
	return socketLister;
});