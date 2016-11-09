<?php

namespace App\Volumio\Services;

use App\Volumio\Services\ConnectionService;
use App\Volumio\Utils\ObjectConverterUtil;
use App\Volumio\Utils\ServiceUtils;

class MusicPlayerService
{
	protected $connectionService;
	protected $currentSongService;

	public function __construct(ConnectionService $connectionService, CurrentSongService $currentSongService)
    {
        $this->connectionService = $connectionService;
        $this->currentSongService = $currentSongService;
    }

    function sendCommand($commandName, $serviceType, $song = null, $playlist = null, $query = null, $searchType = null)
    {
        if ($serviceType)
        {
            $playerClass = ServiceUtils::getClass($serviceType, $this->connectionService);

            $response = "";
            $songClass = "App\\Volumio\\$serviceType\\" . $serviceType . "Song";
            $playlistClass = "App\\Volumio\\$serviceType\\" . $serviceType . "Playlist";

            $song = ObjectConverterUtil::arrayToObject($song, $songClass);
            $playlist = ObjectConverterUtil::arrayToObject($playlist, $playlistClass);
        }

        switch ($commandName)
        {
            case "play":
                $this->stopOtherServices($serviceType);

                $response = $playerClass->$commandName($song);

                break;
            case "addQueue":
            case "add":
            case "image":
            case "rateUp":
            case "rateDown":
            case "removePlaylist":
            case "removeQueue":
                $response = $playerClass->$commandName($song);

                break;
            case "playPlaylist":
                $this->stopOtherServices($serviceType);
                $response = $playerClass->$commandName($playlist, $song);
                break;
            case "addPlaylist":
                $response = $playerClass->$commandName($playlist, $song);
                break;
            case "getPlaylist":
                $response = $playerClass->$commandName($playlist);
                break;
            case "search":
                $response = $playerClass->$commandName($query, $searchType);
                break;
            case "next":
            case "previous":
            case "stop":
            case "pause":
            case "status":
            case "shuffle":
            case "repeat":
            case "clearQueue":
            case "getPlaylists":
            case "openService":
                $response = $playerClass->$commandName();
                break;
            case "currentSong":
                $response = $this->currentSongService->getCurrentSong();

                if (array_key_exists("serviceType", $response)) {
                    $playerClass = ServiceUtils::getClass(ucfirst($response["serviceType"]), $this->connectionService);

                    $response = $playerClass->status();
                }
                break;
            case "getQueue":
                $response = $this->currentSongService->getCurrentSong();

                if (array_key_exists("serviceType", $response)) {
                    $playerClass = ServiceUtils::getClass(ucfirst($response["serviceType"]), $this->connectionService);

                    $response = $playerClass->getQueue();
                }
                break;
            case "getServices":
                $services = config("options.services");
                $musicServices = [];
                foreach($services as $service)
                {
                    $newService["directory"] = strtoupper($service);
                    $newService["serviceType"] = strtoupper($service);
                    $newService["type"] = "Directory";
                    array_push($musicServices, $newService);
                }

                return $musicServices;
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
