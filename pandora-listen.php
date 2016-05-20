<?php
// Pandora listens for messages from pianod tcp 4445
// and sends the information to the db and websocket 8081
require __DIR__ . '/vendor/autoload.php';

Dotenv::load(__DIR__);

use Laravel\Lumen\Application;

$loop = React\EventLoop\Factory::create();

$app = new Application(realpath(__DIR__));

$pusher = $app->make("App\Volumio\WebSockets\Pusher");

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