angular.module("app").filter("urlencode", function() {
	return function(input) {
		return encodeURIComponent(input);
	};
});