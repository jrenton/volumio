<?php

namespace App\Volumio\Spotify;

use App\Volumio\Spotify\Interfaces\ISpotifyRepository;

class SpotifyRepository implements ISpotifyRepository
{
    protected $sock;
    
    function __construct()
    {
        $this->sock = SpotifySocket::getInstance();
    }
    
    function sendCommand($cmd) 
	{
		if ($this->sock) 
		{
			$cmd = $cmd."\n";
			fputs($this->sock, $cmd);
	
			while(!feof($this->sock))
			{
				// fgets() may time out during the wait for response from commands like 'idle'.
				// This loop will keep reading until a response is received, or until the socket closes.
				$output = fgets($this->sock);
	
				if ($output) 
				{
					break;
				}
			}
	
			return json_decode($output, true);
		}
	}
	
	function closeSpopSocket() 
	{
		$this->sendCommand("bye");
        
		fclose($this->sock);
	}
}