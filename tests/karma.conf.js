// Karma configuration
// Generated on Fri Jul 15 2016 16:49:21 GMT+1000 (AEST)

module.exports = function (config) {
    config.set({

        // base path that will be used to resolve all patterns (eg. files, exclude)
        basePath: '..',


        // frameworks to use
        // available frameworks: https://npmjs.org/browse/keyword/karma-adapter
        frameworks: [
           // 'jasmine-ajax',
            'jasmine-jquery',
            'jasmine'
        ],

        // list of files / patterns to load in the browser
        files: [
            'public/js/jquery.js',
            'public/js/bootstrap.js',
            'public/js/jquery-ui.js',
            'resources/assets/googlemapsapi/google_maps_api_v3_25.js',
            'public/js/gmap3.js',
            'public/js/jquery.sse.js',
            'public/js/jquery.jscrollpane.js',
            'public/js/jquery.mousewheel.js',
            'public/js/relative-date.js',
            'public/css/*.css',
            'resources/assets/js/*.js',

            'tests/js-tests/common.testAll.js',
            'tests/js-tests/organisation.testAll.js',
            'tests/js-tests/driver.testAll.js',
            'tests/js-tests/vehicle.testAll.js',
            'tests/js-tests/message.testAll.js',
            'tests/js-tests/user.testAll.js',


            {pattern: 'tests/js-tests/fixtures/*.html', watched: true, included: false, served: true},
            {pattern: 'tests/js-tests/fixtures/*.css', watched: true, included: false, served: true},

        ],


        // list of files to exclude
        exclude: [],


        // preprocess matching files before serving them to the browser
        // available preprocessors: https://npmjs.org/browse/keyword/karma-preprocessor
        preprocessors: {
            '**/*.html': []
        },


        // test results reporter to use
        // possible values: 'dots', 'progress'
        // available reporters: https://npmjs.org/browse/keyword/karma-reporter
        reporters: ['progress'],


        // web server port
        port: 9876,


        // enable / disable colors in the output (reporters and logs)
        colors: true,


        // level of logging
        // possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
        logLevel: config.LOG_DEBUG,


        // enable / disable watching file and executing tests whenever any file changes
        autoWatch: true,


        // start these browsers
        // available browser launchers: https://npmjs.org/browse/keyword/karma-launcher
        browsers: [],


        // Continuous Integration mode
        // if true, Karma captures browsers, runs the tests and exits
        singleRun: true,

        // Concurrency level
        // how many browser should be started simultaneous
        concurrency: Infinity


    })
};
