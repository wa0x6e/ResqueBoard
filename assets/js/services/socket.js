var SocketListener = function($rootScope, event) {
	"use strict";
	var socket = new WebSocket("ws://"+CUBE_URL+"/1.0/event/get");
	var callbacks = [];

	socket.onopen = function() {
		var date = new Date();
		socket.send(JSON.stringify({
			"expression": event,
			"start": date.toISOString()
		}));
	};

	var listenMessage = function() {
		socket.onmessage = function () {
			var args = arguments;
			$rootScope.$apply(function () {
				for (var i in callbacks) {
					callbacks[i].apply(socket, args);
				}
			});
		};
	};

	var registerCallback = function(callback) {
		callbacks.push(callback);
	};

	return {
		onmessage: function (callback) {
			registerCallback(callback);
			listenMessage();
		}
	};
};