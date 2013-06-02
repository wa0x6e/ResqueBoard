angular.module("app").filter("urlencode", function() {
	"use strict";
	return function(input) {
		return encodeURIComponent(input);
	};
});