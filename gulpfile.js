var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function (mix) {
    mix.sass('app.scss')
        .scripts([
            'common.js',
            'driver.js',
            'vehicle.js',
            'organisation.js',
            'message.js',
            'location.js',
            'user.js'
        ], 'public/js/home.js')
        .version([
            "css/app.css",
            "public/js/home.js"
        ])
        .copy(
            'resources/assets/images',
            'public/images')
        .copy(
            'resources/assets/bower/jquery/dist/jquery.js',
            'public/js/jquery.js')
        .copy(
            'resources/assets/bower/bootstrap-sass/assets/javascripts/bootstrap.js',
            'public/js/bootstrap.js')
        .copy(
            'resources/assets/bower/jquery-ui/jquery-ui.js',
            'public/js/jquery-ui.js')
        .copy(
            'resources/assets/bower/jquery-ui/themes/smoothness/jquery-ui.css',
            'public/css/jquery-ui.css')
        .copy(
            'resources/assets/bower/gmap3/dist/gmap3.js',
            'public/js/gmap3.js')
        .copy(
            'node_modules/socket.io-client/socket.io.js',
            'public/js/socket.io.js');



});
