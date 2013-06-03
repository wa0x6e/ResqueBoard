module.exports = function(grunt) {

    var headerComment = function(description) {

        return "/*! <%= pkg.name %> v<%= pkg.version %> (<%= grunt.template.today() %>)\n" +
                " *  ---------------------------------------------\n" +
                " *  <%= pkg.name %> " + description + "\n" +
                " *  <%= pkg.homepage %>\n" +

                " *  Licensed under the <%= pkg.license %> license\n" +
                " *  Copyright 2013 <%= pkg.author.name %>\n" +
                " */\n";
    };

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON("package.json"),
        phpunit: {
            withCoverage: {
                dir: "tests/",
                options: {
                    colors: true,
                    bootstrap: "tests/bootstrap.php",
                    coverageHtml: "./build/coverage/php"
                }
            },
            main: {
                dir: "tests/",
                options: {
                    colors: true,
                    bootstrap: "tests/bootstrap.php",
                    noConfiguration: true
                }
            }
        },
        clean: {
            phpCoverage: ["build/coverage/php"],
            jsCoverage: ["build/coverage/js"]
        },
        watch: {
            php: {
                files: ["tests/**/*.php", "src/ResqueBoard/Lib/**/*.php", "src/ResqueBoard/webroot/index.php"],
                tasks: ["phpunit:main"]
            },
            css: {
                files: ["assets/css/less/main.less"],
                tasks: ["less:main"]
            },
            jsControllers: {
                files: ["assets/js/controllers/*.js"],
                tasks: ["uglify:controllers"]
            },
            jsDirectives: {
                files: ["assets/js/directives/*.js"],
                tasks: ["uglify:directives"]
            },
            jsServices: {
                files: ["assets/js/services/*.js"],
                tasks: ["uglify:services"]
            },
            jsFilters: {
                files: ["assets/js/filters/*.js"],
                tasks: ["uglify:filters"]
            },
            jsMain: {
                files: ["assets/js/*.js"],
                tasks: ["uglify:main"]
            }
        },
        uglify: {
            controllers: {
                files: {
                    "src/ResqueBoard/webroot/js/controllers/workerController.js": ["assets/js/controllers/workerController.js"],
                    "src/ResqueBoard/webroot/js/controllers/jobController.js": ["assets/js/controllers/jobController.js"],
                    "src/ResqueBoard/webroot/js/controllers/lastestJobGraphController.js": ["assets/js/controllers/lastestJobGraphController.js"],
                    "src/ResqueBoard/webroot/js/controllers/queueController.js": ["assets/js/controllers/queueController.js"],
                    "src/ResqueBoard/webroot/js/controllers/lastestJobHeatmapController.js": ["assets/js/controllers/lastestJobHeatmapController.js"],
                    "src/ResqueBoard/webroot/js/controllers/scheduledJobController.js": ["assets/js/controllers/scheduledJobController.js"],
                    "src/ResqueBoard/webroot/js/controllers/pendingJobController.js": ["assets/js/controllers/pendingJobController.js"],
                    "src/ResqueBoard/webroot/js/controllers/loadOverviewController.js": ["assets/js/controllers/loadOverviewController.js"],
                    "src/ResqueBoard/webroot/js/controllers/logActivityController.js": ["assets/js/controllers/logActivityController.js"],
                    "src/ResqueBoard/webroot/js/controllers/jobClassDistributionController.js": ["assets/js/controllers/jobClassDistributionController.js"]
                }
            },
            services: {
                files: {
                    "src/ResqueBoard/webroot/js/services/socket.js": ["assets/js/services/socket.js"],
                    "src/ResqueBoard/webroot/js/services/jobsProcessedCounter.js": ["assets/js/services/jobsProcessedCounter.js"],
                    "src/ResqueBoard/webroot/js/services/jobsSuccessCounter.js": ["assets/js/services/jobsSuccessCounter.js"],
                    "src/ResqueBoard/webroot/js/services/jobsFailedCounter.js": ["assets/js/services/jobsFailedCounter.js"],
                    "src/ResqueBoard/webroot/js/services/workersStartListener.js": ["assets/js/services/workersStartListener.js"],
                    "src/ResqueBoard/webroot/js/services/workersStopListener.js": ["assets/js/services/workersStopListener.js"],
                    "src/ResqueBoard/webroot/js/services/workersPauseListener.js": ["assets/js/services/workersPauseListener.js"],
                    "src/ResqueBoard/webroot/js/services/workersResumeListener.js": ["assets/js/services/workersResumeListener.js"]
                }
            },
            directives: {
                files: {
                    "src/ResqueBoard/webroot/js/directives/graphHorizonChart.js": ["assets/js/directives/graphHorizonChart.js"],
                    "src/ResqueBoard/webroot/js/directives/graphPie.js": ["assets/js/directives/graphPie.js"],
                    "src/ResqueBoard/webroot/js/directives/iconJob.js": ["assets/js/directives/iconJob.js"],
                    "src/ResqueBoard/webroot/js/directives/placeholder.js": ["assets/js/directives/placeholder.js"]
                }
            },
            filters: {
                files: {
                    "src/ResqueBoard/webroot/js/filters/uptime.js": ["assets/js/filters/uptime.js"],
                    "src/ResqueBoard/webroot/js/filters/urlencode.js": ["assets/js/filters/urlencode.js"],
                    "src/ResqueBoard/webroot/js/filters/bspopover.js": ["assets/js/filters/bspopover.js"]
                }
            },
            main: {
                files: {
                    "src/ResqueBoard/webroot/js/main.js": ["assets/js/main.js"],
                    "src/ResqueBoard/webroot/js/app.js": ["assets/js/app.js"],
                    "src/ResqueBoard/webroot/js/bootstrap.js": ["assets/js/bootstrap.js"]
                }
            },
            options: {
                event: ["added", "changed"],
                compress: {
                    sequences: true,
                    dead_code: true,
                    unused: true,
                    join_vars: true,
                    warnings: true
                }

            }
        },
        less: {
            options: {
                paths: ["assets/css/less"],
                yuicompress: true,
                compress: true
            },
            main: {
                files: {
                    "src/ResqueBoard/webroot/css/main.min.css": "assets/css/less/main.less"
                }
            },
            all: {
                files: {
                    "src/ResqueBoard/webroot/css/main.min.css": "assets/css/less/main.less",
                    "src/ResqueBoard/webroot/css/bootstrap.min.css": "assets/css/less/bootstrap.less"
                }
            }
        },
        concat: {
            javascript: {
                options: {
                    banner: headerComment("main javascript file")
                },
                src: ["src/ResqueBoard/webroot/js/main.js"],
                dest: "src/ResqueBoard/webroot/js/main.js"
            },
            css: {
                options: {
                    banner: headerComment("main stylesheet file")
                },
                src: ["src/ResqueBoard/webroot/css/main.min.css"],
                dest: "src/ResqueBoard/webroot/css/main.min.css"
            }
        }
    });

    grunt.loadNpmTasks("grunt-phpunit");
    grunt.loadNpmTasks('grunt-devtools');
    grunt.loadNpmTasks("grunt-contrib-watch");
    grunt.loadNpmTasks("grunt-contrib-requirejs");
    grunt.loadNpmTasks("grunt-contrib-uglify");
    grunt.loadNpmTasks("grunt-contrib-concat");
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-bump');
    grunt.loadNpmTasks('grunt-open');

    grunt.registerTask("default", ["phpunit:withCoverage"]);

    grunt.registerTask("build", ["uglify", "less:main", "concat", "coverage-php"]);

    // When ready to ship
    grunt.registerTask("r-patch", ["phpunit:main", "uglify", "less:main", "bump:patch", "concat"]);
    grunt.registerTask("r-minor", ["phpunit:main", "uglify", "less:main", "bump", "concat"]);
    grunt.registerTask("r-major", ["phpunit:main", "uglify", "less:main", "bump:major", "concat"]);

    grunt.registerTask("coverage-php", ["clean:phpCoverage", "phpunit:withCoverage"]);

};
