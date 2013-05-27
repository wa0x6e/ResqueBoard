angular.module("app").filter("bs:popover", function() {
	return function(linkElement) { console.log(linkElement);
        linkElement.popover();
    };
});