angular.module("app").filter("uptime", function() {
	"use strict";
	return function(input) {
		return moment().from(new Date(input), true);
	};
});