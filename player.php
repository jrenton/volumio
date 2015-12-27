<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Http\WebSockets\PlayerWebSocket;
use App\Http\Services\PandoraService;
use App\Http\Services\ConnectionService;
use App\Http\Sockets\PandoraSocket;

require __DIR__ . '/vendor/autoload.php';

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new PlayerWebSocket(new PandoraService(new ConnectionService(), PandoraSocket::getInstance()))
        )
    ),
    8081
);

$server->run();