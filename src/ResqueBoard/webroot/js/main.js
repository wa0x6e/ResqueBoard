require({
	shim: {
		"controllers/workerController": {
			deps: [
				"app",
				"cubism",
				"services/jobsProcessedCounter",
				"services/jobsSuccessCounter",
				"services/jobsFailedCounter",
				"services/workersStartListener",
				"services/workersStopListener",
				"services/workersPauseListener",
				"services/workersResumeListener"
			]
		},
		"controllers/jobController": {
			deps: [
				"app",
				"services/jobsProcessedCounter",
				"services/jobsFailedCounter"
			]
		},
		"controllers/scheduledJobController": {
			deps: ["app", "calheatmap"]
		},
		"controllers/lastestJobGraphController": {
			deps: ["app"]
		},
		"controllers/lastestJobHeatmapController": {
			deps: ["app", "calheatmap"]
		},
		"controllers/pendingJobController": {
			deps: ["app"]
		},
		"controllers/loadOverviewController": {
			deps: ["app", "d3"]
		},
		"controllers/logActivityController": {
			deps: ["app", "moment", "cookie", "jsrender"]
		},
		"controllers/queueController": {
			deps: [
				"app",
				"services/jobsProcessedCounter",
				"services/workersStartListener",
				"services/workersStopListener"
			]
		},
		"directives/graphHorizonChart": {
			deps: ["app"]
		},
		"directives/graphPie": {
			deps: ["app", "d3"]
		},
		"directives/iconJob": {
			deps: ["app"]
		},
		"directives/placeholder": {
			deps: ["app"]
		},
		"filters/uptime": {
			deps: ["app"]
		},
		"filters/urlencode": {
			deps: ["app"]
		},
		"filters/bspopover": {
			deps: ["app"]
		},
		"libs/angular-resource": {
			deps: ["libs/angular"]
		},
		"services/jobsProcessedCounter": {
			deps: ["app", "services/socket"]
		},
		"services/jobsSuccessCounter": {
			deps: ["app", "services/socket"]
		},
		"services/jobsFailedCounter": {
			deps: ["app", "services/socket"]
		},
		"services/workersStartListener": {
			deps: ["app", "services/socket"]
		},
		"services/workersStopListener": {
			deps: ["app", "services/socket"]
		},
		"services/workersPauseListener": {
			deps: ["app", "services/socket"]
		},
		"services/workersResumeListener": {
			deps: ["app", "services/socket"]
		},
		"app": {
			deps: ["libs/angular", "libs/angular-resource", "ui.bootstrap", "jquery", "bootstrapjs", "highlightjs"]
		},
		"bootstrap": {
			deps: ["app"]
		},
		"bootstrapjs": {
			deps: ["jquery"]
		},
		"calheatmap": {
			deps: ["d3"]
		},
		"cookie" : {
			deps: ["jquery"]
		},
		"cubism": {
			deps: ["d3"]
		},
		"ui.bootstrap": {
			deps: ["libs/angular"]
		}
	},
	paths: {
		jquery: "/js/libs/jquery-2.0.0.min",
		d3: "/js/libs/d3",
		calheatmap: "/js/libs/cal-heatmap",
		highlightjs: "/js/libs/highlightjs-7.3.min",
		moment:  "/js/libs/moment-2.0.0.min",
		cookie: "/js/libs/jquery.cookie-1.1.min",
		cubism: "/js/libs/cubism.v1.2.2.min",
		jsrender: "/js/libs/jquery.jsrender-1.0.min",
		"ui.bootstrap": "/js/libs/ui-bootstrap-tpls-0.3.0.min"
	}
}, ["require",
	"controllers/workerController", "controllers/jobController", "controllers/lastestJobGraphController",
	"controllers/queueController", "controllers/lastestJobHeatmapController", "controllers/scheduledJobController",
	"controllers/pendingJobController", "controllers/loadOverviewController", "controllers/logActivityController",
	"filters/uptime", "filters/urlencode", "filters/bspopover",
	"directives/graphHorizonChart", "directives/graphPie", "directives/iconJob", "directives/placeholder"
	], function(require) {
	return angular.element(document).ready(function() {
		return require(["bootstrap"]);
	});
});
