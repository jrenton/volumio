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

//$app->bind('App\Http\WebApis\IClient', 'GuzzleHttp\Client');

$app->configure('options');
// $this->app->singleton('PandoraSocket', function($app)
// {
//     return new App\Http\Sockets\PandoraSocket();
// });

$app->get('/', function (Illuminate\Http\Request $request) {
    $code = $request->input('code');
    
    if ($code) 
    {
        $clientId = "ab6fd2e9ddd04857947ea58e3e44678a";
        $clientSecret = "9ff97405ccfd47e9b656ae0af0c981a1";
        $body = [
            "code" => $code,
            "grant_type" => "authorization_code",
            "redirect_uri" => "http://homestead.app:8000",
            "client_id" => $clientId,
            "client_secret" => $clientSecret
        ];
        $clientIdSecret = base64_encode($clientId . ":" . $clientSecret);
        //"headers" => ["Authorization: Basic " . $clientIdSecret]
        $client = new \GuzzleHttp\Client();
        $request = $client->request('POST', 'https://accounts.spotify.com/api/token', ["form_params" => $body]);
        $response = json_decode($request->getBody());
        session(["spotify_access_token" => $response->access_token,
                 "spotify_refresh_token" => $response->refresh_token]);
        return redirect("/");
    }
    
    return view('home');
});

$app->get("settings", [
    'as' => 'settings', 'uses' => 'SettingsController@index'
]);

$app->post('settings', [
    'as' => 'settingsPost', 'uses' => 'SettingsController@postIndex'
]);

$app->get("me", function(App\Http\WebApis\SpotifyWebApi $webApi) {
    dd($webApi->getFeaturedPlaylists());
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

$app->get("sources", [
    'as' => 'sources', 'uses' => 'SourcesController@index'
]);

$app->get("netconfig", [
    'as' => 'netconfig', 'uses' => 'NetConfigController@index'
]);

$app->get("mpdconfig", [
    'as' => 'mpdconfig', 'uses' => 'MpdConfigController@index'
]);