<?php
require __DIR__ . '/vendor/autoload.php';

use App\Http\Services\PandoraService;
use App\Http\Services\ConnectionService;
use App\Http\Sockets\PandoraSocket;
use App\Http\WebSockets\PlayerWebSocket;

$loop   = React\EventLoop\Factory::create();
// $loop   = new React\EventLoop\StreamSelectLoop; 
$pusher = new App\Http\WebSockets\PlayerWebSocket;

// Listen for the web server to make a ZeroMQ push after an ajax request
$context = new React\ZMQ\Context($loop);
$pull = $context->getSocket(ZMQ::SOCKET_PULL);
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