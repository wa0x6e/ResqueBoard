angular.module("app").factory("jobsSuccessCounter", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "done");
	return socketLister;
});