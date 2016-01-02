<?php

namespace App\Http\Services;

use App\Http\Services\ConnectionService;
use App\Http\Utils\ObjectConverterUtil;
use App\Http\Utils\ServiceUtils;

class MusicPlayerService
{
	protected $connectionService;
	
	public function __construct(ConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
    }
    
    function sendCommand($commandName, $serviceType, $song = null, $playlist = null, $query = null)
    {
        $playerClass = ServiceUtils::getClass($serviceType, $this->connectionService);
        
        $response = "";
        $songClass = "App\\Http\\Songs\\" . $serviceType . "Song";
        
        switch ($commandName)
        {
            case "play":
                $this->stopOtherServices($serviceType);
            
                if ($song)
                {
                    
                    $response = $playerClass->$commandName(ObjectConverterUtil::arrayToObject($song, $songClass));
                }
                else
                {
                    $response = $playerClass->$commandName();
                }
                
                break;
            case "addQueue":
            case "add":
            case "image":   
                if ($song)
                {
                    
                    $response = $playerClass->$commandName(ObjectConverterUtil::arrayToObject($song, $songClass));
                }
                else
                {
                    $response = $playerClass->$commandName();
                }                   
                break;
            case "playPlaylist":
                $this->stopOtherServices($serviceType);
                $reponse = $playerClass->$commandName($playlist, $song);
                break;
            case "addPlaylist":
                $reponse = $playerClass->$commandName($playlist, $song);
                break;
            case "getPlaylist":
                $reponse = $playerClass->$commandName($playlist);
                break;
            case "search":                
                $response = $response = $playerClass->$commandName($query);
                break;
            case "next": 
            case "previous":
            case "stop":
            case "pause":
            case "status":
            case "shuffle":
            case "repeat":
            case "getQueue":
            case "clearQueue":
            case "getPlaylists":
                $response = $playerClass->$commandName();
                break;
        }
        
        return json_encode($response);
    }
    
    function stopOtherServices($currentServiceType)
    {
        $services = config('options.services');
        
        foreach ($services as $service)
        {
            if ($service == $currentServiceType)
                continue;
            
            $serviceClass = ServiceUtils::getClass($service, $this->connectionService);
        
            $serviceClass->stop();
        }
    }
}
