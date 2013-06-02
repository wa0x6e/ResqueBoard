angular.module("app").directive("placeholder", function() {

	"use strict";

	return {
		restrict: "E",
		template: "<div ng-hide=\"status==1\" class=\"knight-unit smaller\" ng-class=\"{error: status == 3}\">" +
		"<p class=\"tagline\"><i class=\"{{_placeholder.icon}} icon\"></i> " +
		"<span ng-bind-html-unsafe=\"_placeholder.message\"></span></p> " +
		"<button ng-click=\"init()\" ng-show=\"status==3\" class=\"btn btn-small\"><i class=\"icon-refresh\"></i> Retry</button></div>",
		replace: true,
		scope: {
			status: "=",
			dataName: "@contentName",
			loadingDataName: "@loadingContentName",
			icon: "@",
			errorCode: "=",
			errorMessage: "=",
			init: "&"
		},
		link: function (scope) {

			scope._placeholder = {
				message : "",
				icon : ""
			};

			scope.$watch("status", function() {
				if (scope.status === 0) {
					scope._placeholder.icon = "icon-spinner icon-spin";
					scope._placeholder.message = "Loading " + scope.loadingDataName;
				} else if (scope.status === 2) {
					scope._placeholder.icon = scope.icon;
					scope._placeholder.message = "No " + scope.dataName;
				} else if (scope.status === 3) {
					scope._placeholder.icon = "icon-warning-sign";

					if (scope.errorMessage.length > 0) {
						scope._placeholder.message = scope.errorMessage;
					} else {
						scope._placeholder.message = "A <b>" + scope.errorCode + "</b> occured while fetching the " + scope.loadingDataName;
					}
				}
			});
		}
	};
});