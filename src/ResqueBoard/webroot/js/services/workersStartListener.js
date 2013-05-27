angular.module("app").factory("workersStartListener", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "start");
	return socketLister;
});