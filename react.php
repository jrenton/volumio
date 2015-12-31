<?php
require __DIR__ . '/vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$server = stream_socket_client('tcp://127.0.0.1:4445');
stream_set_blocking($server, 0);

// while ($conn = @stream_socket_accept($socket,$nbSecondsIdle))
// {
//     $message = fread($conn, 1024);
//     echo 'I have received that : '.$message;
//     //fputs ($conn, "OK\n");
//     fclose ($conn);
// }
$loop->addReadStream($server, function ($server) use ($loop) {
    //$conn = stream_socket_accept($server);
    $message = fread($server, 1024);
    echo $message;
});

$loop->run();