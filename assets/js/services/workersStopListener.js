angular.module("app").factory("workersStopListener", ["$rootScope", function($rootScope) {
	"use strict";
	var socketLister = new SocketListener($rootScope, "shutdown");
	return socketLister;
}]);