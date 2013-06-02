angular.module("app").factory("jobsSuccessCounter", ["$rootScope", function($rootScope) {
	var socketLister = new SocketListener($rootScope, "done");
	return socketLister;
}]);