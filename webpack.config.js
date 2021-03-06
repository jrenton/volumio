var path = require('path');
var webpack = require('webpack');
var PolyfillsPlugin = require('webpack-polyfills-plugin');

module.exports = {
  cache: true,
  entry: {
    main: './resources/assets/js/main.js',
  },
  output: {
    path: path.resolve(__dirname, './public/js'),
    publicPath: '/',
    filename: '[name].js',
  },
  plugins: [
    new webpack.ContextReplacementPlugin(/moment[\/\\]locale$/, /en/),
    new webpack.ProvidePlugin({
      'Promise': 'imports?this=>global!exports?global.Promise!es6-promise',
      'fetch': 'imports?this=>global!exports?global.fetch!whatwg-fetch'
    }),
    new PolyfillsPlugin([
      'Array/prototype/findIndex',
      'Array/prototype/find',
    ]),
  ],
  externals: {
    'jquery': 'jQuery',
    'moment': 'moment',
  },
  resolveLoader: {
    root: path.join(__dirname, 'node_modules'),
  },
  module: {
    loaders: [
      {
        test: /\.vue$/,
        loader: 'vue',
        exclude: /node_modules/,
      },
      {
        test: /\.js$/,
        loader: 'babel',
        exclude: /node_modules/,
      },
      {
        test: /\.json$/,
        loader: 'json',
      },
      {
        test: /\.html$/,
        loader: 'vue-html',
      },
      {
        test: /\.(png|jpg|gif|svg|ttf)$/,
        loader: 'url',
        query: {
          limit: 10000,
          name: '[name].[ext]?[hash]',
        },
      }
    ]
  },
  devServer: {
    historyApiFallback: true,
    noInfo: true,
  },
  devtool: 'eval',
}

if (process.env.NODE_ENV === 'production') {
  module.exports.devtool = 'source-map'
  // http://vuejs.github.io/vue-loader/workflow/production.html
  module.exports.plugins = (module.exports.plugins || []).concat([
    new webpack.optimize.UglifyJsPlugin({
      compress: {
        warnings: false,
        'drop_console': true,
      },
      output: {
        comments: false,
      },
    }),
    new webpack.optimize.OccurenceOrderPlugin()
  ]);
}
