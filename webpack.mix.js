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
mix.js('resources/js/app.js', 'public/js')
   	.js('vendor/konekt/appshell/src/resources/assets/js/appshell.standalone.js', 'public/js/appshell.js')
   	.sass('vendor/konekt/appshell/src/resources/assets/sass/appshell.sass', 'public/css')
	.sass('resources/sass/app.scss', 'public/css');

mix.js('resources/js/block-note.js', 'public/js/block-note.js').react();

// Use this option if vendor/konekt/appshell is a symlink:
// mix.webpackConfig({ resolve: { symlinks: false } });
