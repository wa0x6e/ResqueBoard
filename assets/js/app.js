angular.module("app", ["ngResource", "ui.bootstrap"]);

// Init syntax highlighter
hljs.initHighlightingOnLoad();

$("body").tooltip({html: true, container: "body", selector: "[data-event~=tooltip]"});
$("[data-event~=popover]").popover({html: true});
$("[data-event~=collapse-all]").on("click", function(e){ e.preventDefault(); $(".collapse.in").collapse("hide"); });
$("[data-event~=expand-all]").on("click", function(e){ e.preventDefault(); $(".collapse").not(".in").collapse("show"); });

/**
 * Use a form select"s options as navigation
 */
$(".navigator").on("change", "select", function () {
    window.location = $("option", this).filter(":selected").val();
});

/**
 * Convert a date to ISO 8601 format
 * @param   Date date A date object
 * @return string An ISO 8601 formatted date
 */
const formatISO = function(date) {
    var format = d3.time.format.iso;
    return format.parse(date); // returns a Date
};

/**
 * Duration of the transition animation
 * @type int
 */
const ANIMATION_DURATION = 1500;

$(".infinite-scroll").infinitescroll({
    navSelector : "ul.pager",
    nextSelector : "ul.pager li.next a",
    itemSelector : ".infinite-scroll li",
    loading: {
        finishedMsg: "No more pages to load.",
        img: "http://www.infinite-scroll.com/loading.gif"
    },
    bufferPx: 5000
});

// Setting the server status icon
var setStatus = function(status) {
    "use strict";

    var prop = {
        message : "The Cube server is online",
        iconClass : "icon-circle",
        class : "status-ok"
    };

    if (status === false) {
        prop.message = "Unable to connect to the Cube server";
        prop.iconClass = "icon-info-sign";
        prop.class = "status-error";
    }

    var dom = $("#server-status li[data-server=cube]");
    dom.removeClass("status-unknown").addClass(prop.class);
    dom.popover("destroy");
    dom.popover({content: prop.message});
};

// Check Cube server status
$.ajax({
    type: "GET",
    url: "//" + CUBE_URL + "/1.0/types/get?",
    dataType: "json",
    statusCode: {
        200: function() {setStatus(true);},
        404: function() {setStatus(false);}
    }
});
