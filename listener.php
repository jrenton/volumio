<?php
require __DIR__ . '/vendor/autoload.php';

use App\Http\Services\PandoraService;
use App\Http\Services\ConnectionService;
use App\Http\Notifiers\SongChangeNotifier;
use App\Http\Sockets\PandoraSocket;

//$loop   = React\EventLoop\Factory::create();
$loop   = new React\EventLoop\StreamSelectLoop; 
$pusher = new App\Http\WebSockets\Pusher(new PandoraService(new ConnectionService, new SongChangeNotifier, PandoraSocket::getInstance()));

$client = stream_socket_client('tcp://127.0.0.1:4445');
stream_set_timeout($client, 0, 100000);
stream_set_blocking($client, 0);

$loop->addReadStream($client, function ($client) use ($loop, $pusher) {
    $message = "";
    while (true)
    {
        $response = fgets($client);
        
        $message .= $response;
        
        if (!$response)
        {
            break;
        }
    }
    
    $pusher->onReceiveMessage($message);
});

// Listen for the web server to make a ZeroMQ push after an ajax request
// $context = new React\ZMQ\Context($loop);
// $pull = $context->getSocket(ZMQ::SOCKET_PULL);
// $pull->connect('tcp://127.0.0.1:4445'); // Binding to 127.0.0.1 means the only client that can connect is itself
// $pull->on('message', array($pusher, 'onBlogEntry'));

// Set up our WebSocket server for clients wanting real-time updates
$webSock = new React\Socket\Server($loop);
$webSock->listen(8081, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
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