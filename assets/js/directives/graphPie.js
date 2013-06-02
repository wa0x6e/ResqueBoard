angular.module("app").directive("graphPie", function() {

	"use strict";

	return {
		restrict: "E",
		template: "<div></div>",
		replace: true,
		scope: {
			processedjobs: "=",
			failedjobs: "="
		},
		link: function (scope, element) {

			var datas = [{
					name : "success",
					count : Math.max(1, scope.processedjobs),
					color: "#aec7e8"
				}, {
					name : "failed",
					count : scope.failedjobs,
					color : "#e7969c"
			}];

			var m = 0;
			var r = (element.parent().width()-m*2)/2;
			var arc = d3.svg.arc().innerRadius(r / 2).outerRadius(r);

			var donut = d3.layout.pie().value(function(d){
				return d.count;
			});

			var svg = d3.select(element[0])
				.append("svg:svg")
				.attr("width", (r + m) * 2)
				.attr("height", (r + m) * 2)
				.append("svg:g")
				.attr("transform", "translate(" + (r + m) + "," + (r + m) + ")")
			;

			svg.selectAll("path")
				.data(donut(datas))
				.enter().append("svg:path")
				.attr("d", arc)
				.attr("fill", function(d) { return d.data.color; })
				.attr("title", function(d){ return d.data.count + " " +	d.data.name + " jobs"; })
				.each(function(d) { this._current = d; })
			;

			var redraw = function(datas) {
				function arcTween(a) {
					var i = d3.interpolate(this._current, a);
					this._current = i(0);
					return function(t) {
						return arc(i(t));
					};
				}

				svg.selectAll("path")
					.data(donut(datas))
					.transition()
					.duration(ANIMATION_DURATION)
					.attrTween("d", arcTween)
				;
			};

			scope.$watch("processedjobs", function (newVal, oldVal) {
				if (newVal !== oldVal) {
					datas[0].count = newVal;
					redraw(datas);
				}
			});

			scope.$watch("failedjobs", function (newVal, oldVal) {
				if (newVal !== oldVal) {
					datas[1].count = newVal;
					redraw(datas);
				}
			});

		}
	};


});