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
            'resources/assets/js/maperizer/jqueryui.maperizer.js',
            'public/js/maperizer/jqueryui.maperizer.js')
        .copy(
            'resources/assets/js/maperizer/List.js',
            'public/js/maperizer/List.js')
        .copy(
            'resources/assets/js/maperizer/map-options.js',
            'public/js/maperizer/map-options.js')
        .copy(
            'resources/assets/js/maperizer/Maperizer.js',
            'public/js/maperizer/Maperizer.js'
        );
});
