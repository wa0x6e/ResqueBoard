angular.module("app").controller("jobClassDistributionController", [
	"$scope", "$http", function($scope, $http) {

    "use strict";

    $scope._init = 0;

    $scope.init = function() {

        var url = "api/jobs/distribution/class";
        if (typeof $scope.limit !== "undefined") {
            url += "/" + $scope.limit;
        }

        $scope.displayedCount = 0;

        $http({method: "GET", url: url}).
            success(function(data) {
                for(var s in data.stats) {
                    data.stats[s].rank = (+s+1);
                    $scope.displayedCount += data.stats[s].count;
                }

                if ($scope.displayedCount < data.total) {
                    data.stats.push({
                        rank: data.stats.length+1,
                        name: "Other",
                        count: data.total - $scope.displayedCount,
                        perc: (data.total - $scope.displayedCount) / data.total * 100
                    });
                }

                $scope.classes = data.stats;
                $scope.total = data.total;

                if ($scope.total === 0) {
                    $scope._init = 2;
                } else {
                    $scope._init = 1;
                    pieChart("jobRepartition", $scope.total, $scope.classes);
                }
            }).
            error(function(data, status) {
                $scope._errorCode = status;
                $scope._init = 3;
        });

    };

    $scope.init();

    // Pie chart

    /**
     * Create a pie chart from a set of data
     *
     * @param   {[type]} id     [description]
     * @param   {[type]} total  [description]
     * @param   {[type]} data   [description]
     */
    function pieChart(id, total, _data)
    {
        var r = 80;
        var ir = 40;
        var textOffset = 14;
        var z = d3.scale.category20();
        var threshold = 5;
        var other = 0;
        var data = [];

        for(var d in _data) {
            if (_data[d].perc < threshold) {
                other += _data[d].perc;
            } else {
                data.push(_data[d]);
            }
        }

        if (other > 0) {
            if (data[data.length-1].name.toLowerCase() === "other") {
                data[data.length-1].perc += other;
            } else {
                data.push({
                    name: "Other",
                    perc: other
                });
            }
        }

        var donut = d3.layout.pie().value(function(d){
            return d.perc;
        });

        var parent = $("#"+id);
        var w = parent.width();
        var h = 250;

        // Define the margin, radius, and color scale. The color scale will be
        // assigned by index, but if you define your data using objects, you could pass
        // in a named field from the data object instead, such as `d.name`. Colors
        // are assigned lazily, so if you want deterministic behavior, define a domain
        // for the color scale.

        var arc = d3.svg.arc()
        .startAngle(function(d){ return d.startAngle; })
        .endAngle(function(d){ return d.endAngle; })
        .innerRadius(ir)
        .outerRadius(r);

        var formatCenterText = function(nb) {
            if (nb > 10000) {
                return Math.round(nb /1000) + "K";
            }
            return nb;
        };

        ///////////////////////////////////////////////////////////
        // GROUP //////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////

        // Insert an svg:svg element (with margin) for each row in our dataset. A
        // child svg:g element translates the origin to the pie center.
        var svg = d3.select(parent[0])
            .append("svg:svg")
            .attr("class", "classyPieChart")
            .attr("width", w)
            .attr("height", h)
        ;

        var arc_group = svg
            .append("svg:g")
            .attr("transform", "translate(" + (w/2) + "," + (h/2) + ")")
        ;

        var label_group = svg
        .append("svg:g")
        .attr("transform", "translate(" + (w/2) + "," + (h/2) + ")");

        var center_group = svg
        .append("svg:g")
        .attr("transform", "translate(" + (w/2) + "," + (h/2) + ")");

        ///////////////////////////////////////////////////////////
        // ARC ////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////

        // The data for each svg:svg element is a row of numbers (an array). We pass
        // that to d3.layout.pie to compute the angles for each arc. These start and end
        // angles are passed to d3.svg.arc to draw arcs! Note that the arc radius is
        // specified on the arc, not the layout.
        arc_group.selectAll("path")
            .data(donut(data))
            .enter().append("svg:path")
            .attr("d", arc)
            .attr("stroke", "white")
            .attr("stroke-width", 0.5)
            .attr("fill", function(d, i) { return z(i); })
            .each(function(d, i) { data[i].startAngle = d.startAngle; data[i].endAngle = d.endAngle; })
        ;

        ///////////////////////////////////////////////////////////
        // PLACEHOLDER ////////////////////////////////////////////
        ///////////////////////////////////////////////////////////

        if (data.length === 0) {
            arc_group.append("svg:circle")
            .attr("fill", "#EFEFEF")
            .attr("r", r);

            center_group.append("svg:circle")
                .attr("fill", "white")
                .attr("r", ir);
        }

        ///////////////////////////////////////////////////////////
        // CENTER TEXT ////////////////////////////////////////////
        ///////////////////////////////////////////////////////////



        // // "TOTAL" LABEL
        center_group.append("svg:text")
            .attr("class", "pie-label")
            .attr("dy", -15)
            .attr("text-anchor", "middle") // text-align: right
            .text("TOTAL")
        ;

        //TOTAL TRAFFIC VALUE
        center_group.append("svg:text")
                .attr("class", "pie-total")
                .attr("dy", 7)
                .attr("text-anchor", "middle") // text-align: right
                .text(formatCenterText(total))
        ;

        //UNITS LABEL
        center_group.append("svg:text")
                .attr("class", "pie-units")
                .attr("dy", 21)
                .attr("text-anchor", "middle") // text-align: right
                .text("jobs")
        ;

        ///////////////////////////////////////////////////////////
        // ARC LABEL //////////////////////////////////////////////
        ///////////////////////////////////////////////////////////

        //DRAW TICK MARK LINES FOR LABELS
        var lines = label_group.selectAll("line").data(data);
        lines.enter().append("svg:line")
        .attr("x1", 0)
        .attr("x2", 0)
        .attr("y1", -r-3)
        .attr("y2", -r-8)
        .attr("stroke", "gray")
        .attr("transform", function(d) {
            return "rotate(" + (d.startAngle+d.endAngle)/2 * (180/Math.PI) + ")";
        });

        //DRAW LABELS WITH PERCENTAGE VALUES
        label_group.selectAll("text.pie-value").data(data)
            .attr("dy", function(d){
                if ((d.startAngle+d.endAngle)/2 > Math.PI/2 && (d.startAngle+d.endAngle)/2 < Math.PI*1.5 ) {
                    return 5;
                } else {
                    return -7;
                }
            })
            .attr("text-anchor", function(d){
                if ( (d.startAngle+d.endAngle)/2 < Math.PI ){
                    return "beginning";
                } else {
                    return "end";
                }
            })
            .enter().append("svg:text")
            .attr("class", "pie-value")
            .attr("transform", function(d) {
                return "translate(" + Math.cos(((d.startAngle+d.endAngle - Math.PI)/2)) * (r+textOffset) + "," + Math.sin((d.startAngle+d.endAngle - Math.PI)/2) * (r+textOffset) + ")";
            })
            .attr("dy", function(d){
                if ((d.startAngle+d.endAngle)/2 > Math.PI/2 && (d.startAngle+d.endAngle)/2 < Math.PI*1.5 ) {
                    return 5;
                } else {
                    return -7;
                }
            })
            .attr("text-anchor", function(d){
                if ( (d.startAngle+d.endAngle)/2 < Math.PI ){
                    return "beginning";
                } else {
                    return "end";
                }
            }).text(function(d){
                return Math.round(d.perc) + "%";
            })
        ;

        //DRAW LABELS WITH ENTITY NAMES
        var nameLabels = label_group
            .selectAll("text.pie-units")
            .data(data)
            .attr("dy", function(d){
                if ((d.startAngle+d.endAngle)/2 > Math.PI/2 && (d.startAngle+d.endAngle)/2 < Math.PI*1.5 ) {
                    return 17;
                } else {
                    return 5;
                }
            })
            .attr("text-anchor", function(d){
                if ((d.startAngle+d.endAngle)/2 < Math.PI ) {
                    return "beginning";
                } else {
                    return "end";
                }
            }).text(function(d){
                return d.name;
            })
        ;

        nameLabels.enter().append("svg:text")
            .attr("class", "pie-units")
            .attr("transform", function(d) {
                return "translate(" + Math.cos(((d.startAngle+d.endAngle - Math.PI)/2)) * (r+textOffset) + "," + Math.sin((d.startAngle+d.endAngle - Math.PI)/2) * (r+textOffset) + ")";
            })
            .attr("dy", function(d){
                if ((d.startAngle+d.endAngle)/2 > Math.PI/2 && (d.startAngle+d.endAngle)/2 < Math.PI*1.5 ) {
                    return 17;
                } else {
                    return 5;
                }
            })
            .attr("text-anchor", function(d){
                if ((d.startAngle+d.endAngle)/2 < Math.PI ) {
                    return "beginning";
                } else {
                    return "end";
                }
            }).text(function(d){
                return d.name;
            })
        ;

    }

}]);
