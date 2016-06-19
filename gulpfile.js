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
        ],'public/js/home.js')
        .version(["css/app.css", "public/js/home.js"]);
});
