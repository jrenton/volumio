<?php
require __DIR__ . '/vendor/autoload.php';

Dotenv::load(__DIR__);

use App\Volumio\Spotify\SpotifySocket;
use Laravel\Lumen\Application;

$loop   = React\EventLoop\Factory::create();

$app = new Application(realpath(__DIR__));

$pusher = $app->make("App\Volumio\Spotify\SpotifySocket");

$client = SpotifySocket::getInstance();

fputs($client, "idle\n");
$message = fgets($client);

$loop->addReadStream($client, function ($client) use ($loop, $pusher) {
    $message = fgets($client);
    fputs($client, "idle\n");
    $pusher->onMessage($message);
});

$loop->run();