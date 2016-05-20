<?php

namespace App\Volumio\Pandora;

use App\Volumio\Services\ConnectionService;
use App\Volumio\Notifiers\SongChangeNotifier;

class PandoraService
{
	protected $connectionService;
	protected $songChangeNotifier;
    private $sock;
	
	public function __construct(ConnectionService $connectionService, SongChangeNotifier $songChangeNotifier)
    {
        $this->connectionService = $connectionService;
        $this->songChangeNotifier = $songChangeNotifier;
        $this->sock = PandoraSocket::getInstance();
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
        
        return $this->parseMessage($message);
    }
    
    function getElapsedTime($response)
    {
        $time = $this->parseTimeFromResponse($response);
        
        if (!is_array($time) || !array_key_exists(0, $time))
        {
            return 0;
        }
        
        return $this->connectionService->convertTimeToSeconds($time[0]);
    }
    
    function getTotalTime($response)
    {
        $time = $this->parseTimeFromResponse($response);
        
        if (!is_array($time) || !array_key_exists(1, $time))
        {
            return 0;
        }
        
        return $this->connectionService->convertTimeToSeconds($time[1]);
    }
    
    function parseTimeFromResponse($response)
    {
        $split = explode(" ", $response);
        
        return explode("/", $split[1]);
    }
    
    function parseMessage($message)
    {
        $messages = explode("\n", $message);
        $returnMessages = array();
        $i = 0;
        $returnMessages[$i] = array();
        $lastResponseCode = 0;
        $hasBeenOther = false;
        $hasMultipleValues = count($messages) > 1;
                
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
            
            $status = "";
            $name = "";
            
            try 
            {
                $status = PandoraEnums::get((int)$responseCode);
                $name = strtolower($status->getName()); 
            }
            catch (\Exception $e) { }           
            
            switch ($responseCode)
            {
                case PandoraEnums::PLAY:
                case PandoraEnums::PAUSE:
                case PandoraEnums::STOP:                                            
                    $returnMessages[$i]["state"] = $name;
                    $returnMessages[$i]["elapsed"] = $this->getElapsedTime($value);
                    $returnMessages[$i]["time"] = $this->getTotalTime($value);
                    $returnMessages[$i]["serviceType"] = "Pandora";
                    
                    break;
                case PandoraEnums::ID:
                case PandoraEnums::ARTIST:
                case PandoraEnums::ALBUM:
                case PandoraEnums::COVERART:
                case PandoraEnums::TITLE:
                case PandoraEnums::RATING:                
                    $returnMessages[$i][$name] = $data;
                    $returnMessages[$i]["serviceType"] = "Pandora";
                    
                    break;      
                case PandoraEnums::STATION:
                    if (array_key_exists($name, $returnMessages[$i]))
                    {
                        $i++;
                    }
                    $returnMessages[$i]["type"] = "RadioStation";
                    $returnMessages[$i]["serviceType"] = "Pandora";
                    $returnMessages[$i]["name"] = $data;
                
                    $returnMessages[$i][$name] = $data;
                    break;
                case 203:
                    if ($hasBeenOther)
                    {
                        $i++;    
                    }
                    
                    $hasBeenOther = true;
                    $returnMessages[$i] = array();
                    break;
                case 204:
                    break;
                default:
                    // if (!$data)
                    // {
                    //     $data = $type;    
                    // }
                                    
                    // if ($responseCode == $lastResponseCode || $lastResponseCode == 203)
                    // {
                    //     array_push($returnMessages[$i], [ $type => $data,
                    //                                      ]);
                    // }
                    // else
                    // {
                    //     $returnMessages[$i][$type] = $data;
                    // }
                    break;
            }
            
            //$returnMessages[$i]["Name"] = $data;
            
            $lastResponseCode = $responseCode;
        }
        
        if (sizeof($returnMessages) == 1 && empty($returnMessages[0]))
        {
            return "";
        }
        
        if (sizeof($returnMessages) == 1)
        {
            //$returnMessages = array_values($returnMessages[0]);
        }
        else
        {
            //$returnMessages = array_values($returnMessages);
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
        $status = $this->getResponse("status");
        
        if (is_array($status)) {
            return $status[0];
        }
        
        return $status;
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
    
    function search($query, $searchType)
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
        return $this->getResponse('play station "' . $playlist->name . '"');
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
        $this->next();
    }
    
    function removeQueue($song)
    {
        
    }
    
    function removePlaylist($song)
    {
        
    }
    
    function openService()
    {
        return $this->getPlaylists();
    }
}
