<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
define('ROOTPATH', $_SERVER['HOME'] . '/' ); // default = '/'
define('DAEMONIP', 'localhost'); // default = 'localhost'
define('ERRORLEVEL', 'E_ERROR'); // default = 'E_ALL ^ E_NOTICE'
define('COMMENTCHAR','#');
define('DELSTRING','');
define('CONFPATH', ROOTPATH.'inc/mpd.conf');
define('DEFCONFPATH', ROOTPATH.'inc/mpd.conf.default');
define('NETCONFPATH', ROOTPATH.'inc/network/interfaces');
define('NETCONFPATHMANUAL', ROOTPATH.'inc/network/interfaces.manual');
define('NETCONFPATHAUTO', ROOTPATH.'inc/network/interfaces.dhcp');
define('NETCONFPATHBOOT', ROOTPATH.'inc/network/interfaces.loadatboot');
define("MPD_RESPONSE_ERR", "ACK");
define("MPD_RESPONSE_OK",  "OK");

$app->configure('options');
// $this->app->singleton('PandoraSocket', function($app)
// {
//     return new App\Http\Sockets\PandoraSocket();
// });

$app->get('/', function () {
    return view('home');
});

$app->get('player2', [
    'as' => 'player2', 'uses' => 'PlayerController@command'
]);

$app->get('playerEngine', [
    'as' => 'playerEngine', 'uses' => 'MpdController@playerEngine'
]);

$app->get('playerEngineSpop', [
    'as' => 'playerEngineSpop', 'uses' => 'SpotifyController@playerEngine'
]);

$app->post('sendCommand', [
    'as' => 'sendCommand', 'uses' => 'PlayerController@sendCommand'
]);

$app->post('player', [
    'as' => 'player', 'uses' => 'MusicPlayerController@index'
]);
