const webpackConfig = require('./webpack.config.js')
webpackConfig.devtool = 'inline-source-map'
webpackConfig.externals = { 'jquery': 'jQuery' }

const doCodeCoverage = (process.env.ENABLE_CODE_COVERAGE)

module.exports = function (config) {
  const _config = {
    // base path that will be used to resolve all patterns (eg. files, exclude)
    basePath: '',

    // frameworks to use
    // available frameworks: https://npmjs.org/browse/keyword/karma-adapter
    frameworks: [ 'mocha', 'chai' ],

    // list of files / patterns to load in the browser
    files: [ 'tests/mocha/tests.bundle.js' ],

    // list of files to exclude
    exclude: [],

    // preprocess matching files before serving them to the browser
    // available preprocessors: https://npmjs.org/browse/keyword/karma-preprocessor
    preprocessors: {
      'tests/mocha/tests.bundle.js': [ 'webpack', 'sourcemap' ]
    },

    // test results reporter to use
    // possible values: 'dots', 'progress'
    // available reporters: https://npmjs.org/browse/keyword/karma-reporter
    reporters: [ 'dots' ],

    webpack: webpackConfig,

    // web server port
    port: 9876,

    // enable / disable colors in the output (reporters and logs)
    colors: true,

    // level of logging
    // possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
    logLevel: config.LOG_INFO,

    // enable / disable watching file and executing tests whenever any file changes
    autoWatch: false,

    // start these browsers
    // available browser launchers: https://npmjs.org/browse/keyword/karma-launcher
    browsers: [ 'PhantomJS' ],

    customLaunchers: {
      IE9: {
        base: 'IE',
        'x-ua-compatible': 'IE=EmulateIE9'
      },
    },

    // Continuous Integration mode
    // if true, Karma captures browsers, runs the tests and exits
    singleRun: true,

    // Concurrency level
    // how many browser should be started simultaneous
    concurrency: Infinity,

    proxies: {
      '/viewerUrl': '/viewerUrl'
    }
  }

  if (doCodeCoverage) {
    _config.reporters.push('coverage')

    _config.coverageReporter = {
      dir: 'coverage/',
      reporters: [{ type: 'json', dir: 'coverage', file: 'js-coverage.json' }]
    }
  }

  config.set(_config)
}
