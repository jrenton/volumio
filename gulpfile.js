var elixir = require('laravel-elixir');

elixir(function(mix) {
    mix.browserify('main.js');
});

console.log(elixir);