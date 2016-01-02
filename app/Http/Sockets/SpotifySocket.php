<?php

namespace App\Http\Sockets;

use App\Http\Notifiers\SongChangeNotifier;
use App\Http\Services\SpotifyService;

class SpotifySocket
{
    private $songChangeNotifier;
    private $spotifyService;
    static $sock;
    
    public function __construct(SpotifyService $spotifyService, SongChangeNotifier $songChangeNotifier)
    {
        $this->songChangeNotifier = $songChangeNotifier;
        $this->spotifyService = $spotifyService;
    }
    
    static function getInstance()
    {
        if (!static::$sock)
        {
            static::$sock = stream_socket_client('tcp://127.0.0.1:6602');
            stream_set_timeout(static::$sock, 0, 100000);
            //stream_set_blocking(static::$sock, 0);

			while(!feof(static::$sock))
			{
				// fgets() may time out during the wait for response from commands like 'idle'.
				// This loop will keep reading until a response is received, or until the socket closes.
				$output = fgets(static::$sock);
                
                if (!$output)
                {
                    break;
                }
			}
        }
        
        return static::$sock;
    }
    
    public function onMessage($message)
    {
        $song = (array)json_decode($message);
        
        $parsedSong = $this->spotifyService->formatResponse($song, $song);
        
        echo json_encode($parsedSong);
        
        $this->songChangeNotifier->notify($parsedSong);
    }
}
