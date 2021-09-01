const mix = require('laravel-mix');

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

mix.react('resources/js/app.js', 'public/js')
  .sass('resources/sass/app.scss', 'public/css');

mix.webpackConfig({
  watchOptions: {
    aggregateTimeout: 2000,
    poll: 2000,
    ignored: [
      /node_modules/,
      /app/,
      /bootstrap/,
      /config/,
      /database/,
      /storage/,
      /vendor/
    ]
  }
});
