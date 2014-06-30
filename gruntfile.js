module.exports = function (grunt) {
    var dwSrc = 'dw.js/src/';

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        /************************************************************************\
         * clean                                                                *
        \************************************************************************/

        clean: {
            dwjs: ['tmp/dw.js']
        },

        /************************************************************************\
         * concat                                                               *
        \************************************************************************/

        concat: {
            dwjs: {
                src: [
                    dwSrc + 'dw.start.js',
                    dwSrc + 'dw.dataset.js',
                    dwSrc + 'dw.column.js',
                    dwSrc + 'dw.column.types.js',
                    dwSrc + 'dw.datasource.js',
                    dwSrc + 'dw.datasource.delimited.js',
                    dwSrc + 'dw.utils.js',
                    dwSrc + 'dw.utils.filter.js',
                    dwSrc + 'dw.chart.js',
                    dwSrc + 'dw.visualization.js',
                    dwSrc + 'dw.visualization.base.js',
                    dwSrc + 'dw.theme.js',
                    dwSrc + 'dw.theme.base.js',
                    dwSrc + 'dw.end.js'
                ],
                dest: 'tmp/dw.js'
            },

            vendor: {
                options: {
                    separator: '\n;\n'
                },
                src: [
                    'www/static/vendor/require-js/require-2.1.8.min.js',
                    'www/static/vendor/jquery/jquery-1.10.2.min.js',
                    'www/static/vendor/underscore/underscore-1.5.2.min.js',
                    'www/static/vendor/cryptojs/hmac-sha256.js',
                    'www/static/vendor/globalize/globalize.min.js'
                ],
                dest: 'www/static/js/vendors.min.js'
            }
        },

        /************************************************************************\
         * uglify                                                               *
        \************************************************************************/

        uglify: {
            dwjs: {
                options: {
                    banner: '/*! <%= pkg.name %> app | version <%= pkg.version %> | <%= grunt.template.today("dd-mm-yyyy") %> */\n'
                },
                files: {
                    'www/static/js/dw-2.0.min.js': ['tmp/dw.js']
                }
            }
        }
    });

    // load tasks
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    // register custom tasks
    grunt.registerTask('dwjs', ['clean:dwjs', 'concat:dwjs', 'uglify:dwjs']);
    grunt.registerTask('vendor', ['concat:vendor']);

    // register default task
    grunt.registerTask('default', ['dwjs', 'vendor']);
};
