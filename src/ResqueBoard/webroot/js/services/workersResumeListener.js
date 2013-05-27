angular.module("app").factory("workersResumeListener", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "resume");
	return socketLister;
});