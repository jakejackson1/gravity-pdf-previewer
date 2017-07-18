const webpack = require('webpack')
const CopyWebpackPlugin = require('copy-webpack-plugin');
const PROD = (process.env.NODE_ENV === 'production')

const ExtractTextPlugin = require("extract-text-webpack-plugin");

const extractSass = new ExtractTextPlugin({
  filename:  (getPath) => {
    return getPath('css/[name].min.css').replace('css/js', 'css');
  },
});

module.exports = {
  entry: {
    'js/previewer': './assets/js/main.previewer.js',
  },
  output: {
    path: __dirname + '/dist/',
    filename: '[name].min.js'
  },
  devtool: PROD ? 'source-map' : 'eval-cheap-module-source-map',
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['env']
          }
        }
      },

      {
        test: /\.scss$/,
        use: extractSass.extract({
          use: [{
            loader: "css-loader",
            options: {
              minimize: true,
              sourceMap: true,
              autoprefixer: {
                add: true,
                cascade: false,
              },
            }
          }, {
            loader: "sass-loader",
            options: {
              sourceMap: true
            }
          }]
        })
      }
    ]
  },
  externals: {
    'jquery': 'jQuery',
  },

  plugins: [
    new webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: JSON.stringify('production')
      }
    }),

    extractSass,

    new webpack.optimize.UglifyJsPlugin({
      compress: {
        warnings: false
      },
      output: {
        comments: false
      },
      sourceMap: false
    }),

    new CopyWebpackPlugin([{
      from: 'node_modules/pdfjs-dist-viewer-min/build/minified/',
      to: __dirname + '/dist/viewer/'
    },
      {
        from: 'assets/viewer.html',
        to: __dirname + '/dist/viewer/web/viewer.html',
        force: true
      }])
  ]
}