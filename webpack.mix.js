let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css');

// Workaround
// https://github.com/JeffreyWay/laravel-mix/issues/1483#issuecomment-366685986
// https://github.com/JeffreyWay/laravel-mix/issues/1437
Mix.listen('configReady', (webpackConfig) => {
  if (Mix.isUsing('hmr')) {
    // Remove leading '/' from entry keys
    webpackConfig.entry = Object.keys(webpackConfig.entry).reduce((entries, entry) => {
      entries[entry.replace(/^\//, '')] = webpackConfig.entry[entry];
      return entries;
    }, {});

    // Remove leading '/' from ExtractTextPlugin instances
    webpackConfig.plugins.forEach((plugin) => {
      if (plugin.constructor.name === 'ExtractTextPlugin') {
        plugin.filename = plugin.filename.replace(/^\//, '');
      }
    });
  }
});
