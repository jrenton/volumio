<?php

namespace App\Http\Services;

use App\Http\Services\ConnectionService;
use App\Http\Sockets\PandoraSocket;

class PandoraService
{
	protected $connectionService;
    private $sock;
	
	public function __construct(ConnectionService $connectionService, $pandoraSocket = null)
    {
        $this->connectionService = $connectionService;
        if (!$pandoraSocket)
        {
            $pandoraSocket = PandoraSocket::getInstance();
        }
        $this->sock = $pandoraSocket;
    }
    
    function sendCommand($cmd) 
	{
        $output = "";
        
		if ($this->sock) 
		{
			$cmd = $cmd."\n";
			$status = fputs($this->sock, $cmd);
	
            $i = 0;
            
			while(!feof($this->sock))
			{
				// fgets() may time out during the wait for response from commands like 'idle'.
				// This loop will keep reading until a response is received, or until the socket closes.
				$response = fgets($this->sock);
                
                //echo $response;
	
                $output .= $response;
                
                if (!$response)
                {
                    break;
                }
			}        
		}
        
        return $output;
	}
    
    function getResponse($command)
    {
        $message = $this->sendCommand($command);
        
        $messages = explode("\n", $message);
        $returnMessages = array();
        $i = 0;
        $lastResponseCode = 0;
        $hasBeenOther = false;
                
        foreach ($messages as $value)
        {
            preg_match("/^(\d{3})\s+([A-Za-z]*)\:?(.*)/", $value, $matches);
                        
            if (!$matches)
            {
                continue;
            }
            
            $responseCode = trim($matches[1]);
            $type = trim($matches[2]);
            $data = trim($matches[3]);
            
            if ($responseCode == 203)
            {
                if ($hasBeenOther)
                {
                    $i++;    
                }
                
                $hasBeenOther = true;
                $returnMessages[$i] = array();
            } 
            else if ($responseCode != 203 && $responseCode != 204)
            {
                if (!$data)
                {
                    $data = $type;    
                }
                                
                if ($responseCode == $lastResponseCode || $lastResponseCode == 203)
                {
                    array_push($returnMessages[$i], $data);
                }
                else
                {
                    $returnMessages[$i][$type] = $data;
                }
            }
            
            $lastResponseCode = $responseCode;
        }
                
        if (sizeof($returnMessages) == 1)
        {
            $returnMessages = array_values($returnMessages[0]);
        }
        else
        {
            $returnMessages = array_values($returnMessages);
        }
        
        return $returnMessages;
    }
    
    function play($song = null)
    {
        $this->sendCommand("play");
    }
    
    function stop()
    {
        $this->sendCommand("stop now");
    }
    
    function pause()
    {
        $this->sendCommand("pause");
    }
    
    function next()
    {
        $this->sendCommand("skip");
    }
    
    function previous()
    {
        
    }
    
    function status()
    {
        return $this->getResponse("status");
    }
    
    function image()
    {
        
    }
    
    function repeat()
    {
        
    }
    
    function shuffle()
    {
        
    }
    
    function search($query)
    {
        return $this->getResponse("find any " . $query);
    }
    
    function getQueue()
    {
        return $this->getResponse("queue");
    }
    
    function clearQueue()
    {
        
    }
    
    function add($song)
    {
        
    }
    
    function addPlaylist($playlist, $song = null)
    {
        $create = "";
        if ($song)
        {
            $create = "song " . $song->id;
        }
        else
        {
            $create = "artist " . $song->artist;
        }
        
        $this->sendCommand("create station named " . $playlist->name . " from " . $create);
    }
    
    function playPlaylist($playlist, $song = null)
    {
        $this->sendCommand("play station " . $playlist->name);
    }
    
    function getPlaylist($playlist)
    {
        $this->playPlaylist($playlist);
    }
    
    function getPlaylists()
    {
        return $this->getResponse("stations");
    }
    
    function rateUp($song)
    {
        $this->sendCommand("rate good " . $song->id);
    }
    
    function rateDown($song)
    {
        $this->sendCommand("rate bad " . $song->id);
    }
}
