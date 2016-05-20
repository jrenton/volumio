<?php
// Song changes is responsible for listening on port 4500
// which is used when a song is changed (via SongChangeNotifier), and then 
// sending the message to web socket port 8082
require __DIR__ . '/vendor/autoload.php';

Dotenv::load(__DIR__);

use Laravel\Lumen\Application;

$app = new Application(realpath(__DIR__));

$pusher = $app->make("App\Volumio\WebSockets\PlayerWebSocket");

$loop   = React\EventLoop\Factory::create();
//$pusher = new App\Volumio\WebSockets\PlayerWebSocket;

// Listen for the web server to make a ZeroMQ push after an ajax request
$context = new React\ZMQ\Context($loop);
$pull = $context->getSocket(ZMQ::SOCKET_PULL);

// Listen to any song changes over port 4500
$pull->bind('tcp://127.0.0.1:4500'); // Binding to 127.0.0.1 means the only client that can connect is itself
$pull->on('message', array($pusher, 'onReceiveMessage'));

// Set up our WebSocket server for clients wanting real-time updates
$webSock = new React\Socket\Server($loop);
$webSock->listen(8082, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
$webServer = new Ratchet\Server\IoServer(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(
            new Ratchet\Wamp\WampServer(
                $pusher
            )
        )
    ),
    $webSock
);

$loop->run();