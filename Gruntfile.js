module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON("package.json"),
        phpunit: {
            classes: {
                dir: 'tests/'
            },
            options: {
                colors: true
            }
        },
        watch: {
            php: {
                files: ['tests/**/*.php', 'src/ResqueBoard/Lib/DateHelper', 'src/ResqueBoard/Lib/Resque/*.php'],
                tasks: ['phpunit']
            }
        }
    });

    grunt.loadNpmTasks('grunt-phpunit');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', ['phpunit']);
};