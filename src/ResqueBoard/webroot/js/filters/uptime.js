angular.module("app").filter("uptime", function() {
	return function(input) {
		return moment().from(new Date(input), true);
	};
});