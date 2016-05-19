<?php
require __DIR__ . '/vendor/autoload.php';

use App\Volumio\Notifiers\SongChangeNotifier;
use App\Volumio\Services\SpotifyService;
use App\Volumio\Services\ConnectionService;
use App\Volumio\Spotify\SpotifySocket;

$loop   = React\EventLoop\Factory::create();
$client = SpotifySocket::getInstance();

$pusher = new SpotifySocket(new SpotifyService(new ConnectionService, new SongChangeNotifier, $client), new SongChangeNotifier);

fputs($client, "idle\n");
$message = fgets($client);

$loop->addReadStream($client, function ($client) use ($loop, $pusher) {
    $message = fgets($client);
    fputs($client, "idle\n");
    $pusher->onMessage($message);
});

// Set up our WebSocket server for clients wanting real-time updates
// $webSock = new React\Socket\Server($loop);
// $webSock->listen(8081, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
// $webServer = new Ratchet\Server\IoServer(
//     new Ratchet\Http\HttpServer(
//         new Ratchet\WebSocket\WsServer(
//             new Ratchet\Wamp\WampServer(
//                 $pusher
//             )
//         )
//     ),
//     $webSock
// );

$loop->run();