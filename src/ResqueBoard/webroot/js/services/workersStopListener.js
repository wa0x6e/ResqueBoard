angular.module("app").factory("workersStopListener", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "shutdown");
	return socketLister;
});