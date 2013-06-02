angular.module("app").filter("bs:popover", function() {
	"use strict";
	return function(linkElement) {
        linkElement.popover();
    };
});