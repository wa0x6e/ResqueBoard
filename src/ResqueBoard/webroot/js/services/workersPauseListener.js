angular.module("app").factory("workersPauseListener", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "pause");
	return socketLister;
});