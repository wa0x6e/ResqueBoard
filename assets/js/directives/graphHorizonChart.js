angular.module("app").directive("graphHorizonChart", function() {

	"use strict";

	return {
		restrict: "E",
		template: "<div></div>",
		replace: true,
		scope: {
			workers: "=",
			length: "="
		},
		link: function (scope, element) {

			var
				context = cubism.context().size(466),
				cube = context.cube("http://"+CUBE_URL),
				horizon = context.horizon().metric(cube.metric).height(element.parent().parent().height()),
				metrics = [];

			var redraw = function() {

				metrics = [];
				element.empty();

				for(var k in scope.workers) {
					metrics.push("sum(done.eq(worker, \"" + scope.workers[k].id + "\"))");
				}

				d3.select(element[0]).selectAll(".horizon")
					.data(metrics)
					.enter().append("div")
					.attr("class", "horizon")
					.call(horizon.extent([-180, 180]).title(null));

				d3.select(element[0]).append("div")
					.attr("class", "rule")
					.call(context.rule());

				d3.select(element[0]).append("div")
					.attr("class", "axis")
					.call(context.axis().orient("bottom").ticks(d3.time.minutes, 10).tickSize(6,3,0)
					.tickFormat(d3.time.format("%H:%M")));
			};

			redraw();

			scope.$watch("length", function (newVal, oldVal) {
				if (newVal !== oldVal) {
					redraw();
				}
			});
		}
	};
});