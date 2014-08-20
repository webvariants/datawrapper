module.exports = function (grunt) {
    var dwSrc  = 'dw.js/src/';
    var cssDir = 'www/static/css/';

    grunt.initConfig({
        pkg:     grunt.file.readJSON('package.json'),
        dwjsPkg: grunt.file.readJSON('dw.js/package.json'),

        /************************************************************************\
         * clean                                                                *
        \************************************************************************/

        clean: {
            dwjs:   ['dw.js/dist/', 'www/static/js/vendors.min.js', 'www/static/js/dw-2.0.min.js'],
            assets: [cssDir + '**/*.min.css']
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
                dest: 'dw.js/dist/dw-2.0.js'
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
         * copy                                                                 *
        \************************************************************************/

        copy: {
            dwjs: {
                files: [
                    { src: 'dw.js/dist/dw-2.0.min.js', dest: 'www/static/js/dw-2.0.min.js' }
                ]
            }
        },

        /************************************************************************\
         * cssmin                                                               *
        \************************************************************************/

        cssmin: {
            assets: {
                expand: true,
                cwd: cssDir,
                src: ['**/*.css', '!*.min.css'],
                dest: cssDir,
                ext: '.min.css'
            }
        },

        /************************************************************************\
         * uglify                                                               *
        \************************************************************************/

        uglify: {
            dwjs: {
                options: {
                    banner: '/*! <%= dwjsPkg.name %> | version <%= dwjsPkg.version %> | <%= grunt.template.today("dd-mmm-yyyy") %> */\n'
                },
                files: {
                    'dw.js/dist/dw-2.0.min.js': ['dw.js/dist/dw-2.0.js']
                }
            }
        },

        /************************************************************************\
         * shell                                                                *
        \************************************************************************/

        shell: {
            propel: {
                command: 'phing -f ../../vendor/propel/propel1/generator/build.xml -Dproject.dir=../../db/',
                options: {
                    execOptions: {
                        cwd: 'vendor/bin'
                    }
                }
            }
        }
    });

    // load tasks
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-shell');

    grunt.registerTask('default', ['dwjs', 'assets']);

    grunt.registerTask('dwjs', ['clean:dwjs', 'concat:dwjs', 'uglify:dwjs']);
    grunt.registerTask('assets', ['concat:vendor', 'copy:dwjs', 'cssmin:assets']);
    grunt.registerTask('propel', ['shell:propel']);
};
