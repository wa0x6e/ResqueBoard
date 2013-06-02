angular.module("app").factory("workersResumeListener", ["$rootScope", function($rootScope) {
	"use strict";
	var socketLister = new SocketListener($rootScope, "resume");
	return socketLister;
}]);