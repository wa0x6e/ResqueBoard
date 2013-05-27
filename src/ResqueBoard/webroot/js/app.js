angular.module('app', ['ngResource', 'ui.bootstrap']);

// Init syntax highlighter
hljs.initHighlightingOnLoad();

$("[data-event~=tooltip]").tooltip({html: true, container: "body"});
$("[data-event~=collapse-all]").on("click", function(e){ e.preventDefault(); $(".collapse.in").collapse("hide"); });
$("[data-event~=expand-all]").on("click", function(e){ e.preventDefault(); $(".collapse").not(".in").collapse("show"); });

/**
 * Use a form select's options as navigation
 */
$(".navigator").on("change", "select", function () {
    window.location = $("option", this).filter(":selected").val();
});

/**
 * Convert a date to ISO 8601 format
 * @param   Date date A date object
 * @return string An ISO 8601 formatted date
 */
var formatISO = function(date) {
    var format = d3.time.format.iso;
    return format.parse(date); // returns a Date
};

/**
 * Duration of the transition animation
 * @type int
 */
var duration = 1500;