angular.module("app").factory("workersStartListener", ["$rootScope", function($rootScope) {
	"use strict";
	var socketLister = new SocketListener($rootScope, "start");
	return socketLister;
}]);