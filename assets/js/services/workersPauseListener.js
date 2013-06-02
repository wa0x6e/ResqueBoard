angular.module("app").factory("workersPauseListener", ["$rootScope", function($rootScope) {
	"use strict";
	var socketLister = new SocketListener($rootScope, "pause");
	return socketLister;
}]);