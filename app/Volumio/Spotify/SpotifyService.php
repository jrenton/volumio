<?php

namespace App\Volumio\Spotify;

use App\Volumio\Services\IMusicPlayerService;
use App\Volumio\Services\ConnectionService;
use App\Volumio\Notifiers\SongChangeNotifier;
use App\Volumio\Spotify\Interfaces\ISpotifyRepository;
use App\Volumio\WebApis\WebApi;

class SpotifyService implements IMusicPlayerService
{
	private $songChangeNotifier;
    private $repo;
    private $api;
    
	public function __construct(ConnectionService $connectionService, SongChangeNotifier $songChangeNotifier)
    {
        $this->songChangeNotifier = $songChangeNotifier;
        $this->repo = new SpotifyRepository;
        $this->api = new SpotifyWebApi(new WebApi(new \GuzzleHttp\Client));
    }
    
    function sendCommand($command)
    {
        return $this->repo->sendCommand($command);
    }
	
	// Return state array for spop daemon.
	function getSpopState($mode) 
	{
		$arrayReturn = array();
	
		$arrayResponse = array();
	
		if (strcmp($mode, "CurrentState") == 0) 
		{
			// Return the current state array
			$arrayResponse = $this->sendCommand("status");
		} 
		else if (strcmp($mode, "NextState") == 0) 
		{
			// Return a state array when a change has occured
			$arrayResponse = $this->sendCommand("idle");
		}
	
		if($arrayResponse) 
		{
			$arrayReturn = $arrayResponse;
		}
	
        $arrayReturn = $this->formatResponse($arrayResponse, $arrayReturn);
        
		return $arrayReturn;
	}
    
    function formatResponse($arrayResponse, $arrayReturn = array())
    {
		// Format the response to be understandable by Volumio
		if (array_key_exists("status", $arrayResponse) == TRUE) 
		{
			if (strcmp($arrayResponse["status"], "stopped") == 0) 
			{
				$arrayReturn["state"] = "stop";
			} 
			else if (strcmp($arrayResponse["status"], "playing") == 0) 
			{
				$arrayReturn["state"] = "play";
			} 
			else if (strcmp($arrayResponse["status"], "paused") == 0) 
			{
				$arrayReturn["state"] = "pause";
			} 
			else 
			{
				$arrayReturn["state"] = $arrayResponse["status"];
			}
		}
		
		if (array_key_exists("repeat", $arrayResponse) == TRUE) 
		{
			if ($arrayResponse["repeat"] == TRUE) 
			{
				$arrayReturn["repeat"] = 1;
			} 
			else 
			{
				$arrayReturn["repeat"] = 0;
			}
		}
	
		if (array_key_exists("shuffle", $arrayResponse) == TRUE) 
		{
			if ($arrayResponse["shuffle"] == TRUE)
			{
				$arrayReturn["random"] = 1;
			} 
			else 
			{
				$arrayReturn["random"] = 0;
			}
		}
	
		if (array_key_exists("position", $arrayResponse) == TRUE && array_key_exists("duration", $arrayResponse) == TRUE) 
		{
			$nTimeElapsed = round($arrayResponse["position"]);
			$nTimeTotal = round($arrayResponse["duration"] / 1000);
	
			if ($nTimeElapsed != 0) 
			{
				$nSeekPercent = round(($nTimeElapsed*100)/$nTimeTotal);
			} 
			else 
			{
				$nSeekPercent = 0;
			}
	
			$arrayReturn["song_percent"] = $nSeekPercent;
			$arrayReturn["elapsed"] = $nTimeElapsed;
			$arrayReturn["time"] = $nTimeTotal;
		}
	
		if (array_key_exists("current_track", $arrayResponse) == TRUE && array_key_exists("total_tracks", $arrayResponse) == TRUE) 
		{
			$arrayReturn["song"] = $arrayResponse["current_track"] - 1;
			$arrayReturn["playlistlength"] = $arrayResponse["total_tracks"];
		}
	
		$arrayReturn["single"] = 0;
		$arrayReturn["consume"] = 0;
        $arrayReturn["serviceType"] = "spotify";
        
        return $arrayReturn;
    }
	
	// Perform Spotify database query/search
	function querySpopDB($queryType, $queryString = "") 
	{
		if (strcmp($queryType, "filepath") == 0) 
		{
			return $this->_getSpopListing($queryString);
		} 
		else if (strcmp($queryType, "file") == 0) 
		{
			return $this->search($queryString);
		}
	
		return array();
	}
    
	// Make an array describing the requested level of the Spop database
	function _getSpopListing($queryString) 
	{
		$arrayReturn = array();
	
		if (strcmp($queryString, "") == 0) 
		{
			// The SPOTIFY root item is requested
			$arrayRootItem = array();
			$arrayRootItem["directory"] = "SPOTIFY";
			$arrayRootItem["type"] = "Directory";
			$arrayRootItem["serviceType"] = "Spotify";
			$arrayRoot = array(0 => $arrayRootItem);
			$arrayReturn = $arrayRoot;
		} 
		else if (strncmp($queryString, "SPOTIFY", 7) == 0) 
		{
			// Looking into the SPOTIFY folder
			$arrayResponse = $this->sendCommand("ls");
			$arrayQueryStringParts = preg_split( "(@|/)", $queryString);
			$nQueryStringParts = count($arrayQueryStringParts);
			$sCurrentDirectory = "SPOTIFY";
			$sCurrentDisplayPath = "SPOTIFY";
	
			$i = 1;
			while ($i < $nQueryStringParts) 
			{
				$sCurrentDirectory = $sCurrentDirectory . "/" . $arrayQueryStringParts[$i];
				if (isset($arrayResponse["playlists"][$arrayQueryStringParts[$i]]["index"]) && $arrayResponse["playlists"][$arrayQueryStringParts[$i]]["index"] == 0) 
                {
					$sCurrentDisplayPath = $sCurrentDisplayPath . "/" . "Starred";
				} 
				else 
				{
					$sCurrentDisplayPath = $sCurrentDisplayPath . "/" . $arrayResponse["playlists"][$arrayQueryStringParts[$i]]["name"];
				}
	
				if (strcmp($arrayResponse["playlists"][$arrayQueryStringParts[$i]]["type"], "playlist") == 0) 
                { 
				    // This is a playlist, navigate into it and stop
					$arrayResponse = $this->sendCommand("ls " . $arrayResponse["playlists"][$arrayQueryStringParts[$i]]["index"]);
					break;
				} 
                else 
                {
				    // Index further into the directory listing
					$arrayResponse = $arrayResponse["playlists"][$arrayQueryStringParts[$i]];
				}
	
				$i++;
			}
	
			$arrayCurrentEntry = array();
			$arrayCurrentEntry["DisplayPath"] = $sCurrentDisplayPath;
			array_push($arrayReturn, $arrayCurrentEntry);
	
			$i = 0;
			if (isset($arrayResponse["tracks"])) 
            {
                $arrayReturn = $this->parseSongsResponse($arrayResponse, $arrayReturn);
			} 
            else if (isset($arrayResponse["playlists"])) 
            {
                $arrayReturn = $this->parsePlaylistsResponse($arrayResponse, $arrayReturn);
			}
		}
	
		return $arrayReturn;
	}
    
    function parsePlaylistResponse($response, $arrayReturn = array())
    {
        $arrayReturn["name"] = $response["name"];
        $arrayReturn["offline"] = $response["offline"];
        $arrayReturn["songs"] = $this->parseSongsResponse($response);
        
        return $arrayReturn;
    }
    
    function parsePlaylistsResponse($response, $arrayReturn = array())
    {
        if (!array_key_exists("playlists", $response))
        {
            return $arrayReturn;
        }
        
        // This is a browsable listing
        foreach ($response["playlists"] as $playlist) 
        {
            $arrayCurrentEntry = $playlist;
            $arrayCurrentEntry["serviceType"] = "Spotify";
            $sItemDisplayName = $playlist["name"];

            if (strcmp($playlist["type"], "playlist") == 0) 
            {
                // This is a browsable playlist
                //$sItemDirectory = $sCurrentDirectory . "/" . $i . "@" . $playlist["index"];
                $arrayCurrentEntry["id"] = $playlist["index"];

                if ($playlist["index"] == 0) 
                {
                    $sItemDisplayName = "Starred";
                }
            } 
            else 
            {
                // This is a Spotify folder
                //$sItemDirectory = $sCurrentDirectory . "/" . $i;
            }

            //$arrayCurrentEntry["directory"] = $sItemDirectory;
            // $arrayCurrentEntry["DisplayName"] = $sItemDisplayName;
            array_push($arrayReturn, $arrayCurrentEntry);
        }
        
        return $arrayReturn;
    }
    
    function parseSongsResponse($response, $arrayReturn = array())
    {        
        if (isset($response["tracks"]))
        {
            $songs = $response["tracks"];
            foreach ($songs as $song)
            {
                array_push($arrayReturn, $this->parseSongResponse($song));                
            }
        }
        
        return $arrayReturn;
    }
    
    function parseSongResponse($song)
    {
        $arrayCurrentEntry = $song;
        $arrayCurrentEntry["type"] = "song";
        $arrayCurrentEntry["serviceType"] = "Spotify";
        $arrayCurrentEntry["id"] = (string)$song["uri"];
        $arrayCurrentEntry["time"] = $song["duration"];
        
        $position = 0;

        if (array_key_exists("position", $song)) {
            $arrayCurrentEntry["elapsed"] = $song["position"];
        }
        
        return $arrayCurrentEntry;
    }
    
    function play($song = null)
    {
        if (!$song)
        {
            $this->sendCommand("play");
        }
        else if ($song->uri)
        {
            $this->sendCommand("uplay " . $song->uri);
            $this->songChangeNotifier->notify($song);
        }
    }
    
    function stop()
    {
        $this->sendCommand("stop");
    }
    
    function pause()
    {
        $this->sendCommand("toggle");
    }
    
    function next()
    {
        $this->sendCommand("next");
    }
    
    function previous()
    {
        $this->sendCommand("prev");
    }
    
    function status()
    {
        return $this->parseSongResponse($this->sendCommand("status"));
    }
    
    function image($song = null)
    {
        $base64Image = [];
        if (!$song)
        {
            $base64Image = $this->sendCommand("image");
        }
        else
        {
            $base64Image = $this->sendCommand("uimage " . $song->uri);
        }
        
        if (!array_key_exists("data", $base64Image))
        {
            return null;
        }
        
        $song = new \stdClass;
        $song->base64 = $base64Image["data"];
        
        return $song;
    }
    
    function repeat()
    {
        $tnis->sendCommand("repeat");
    }
    
    function shuffle()
    {
        $tnis->sendCommand("shuffle");
    }
    
    function search($query, $searchType)
    {
        $db = [];
        $db["query"] = $query;
        $db["searchType"] = $searchType;
        
        //dd($db);
        
        if (!$searchType) 
        {
           $response = $this->sendCommand("search \"" . $query . "\"");
           
           return $this->parseSongsResponse($response); 
        }
        
        $response = $this->api->search($query, $searchType);
		
		return $this->parseSongsResponse($response);
    }
    
    function getQueue()
    {
        return $this->sendCommand("qls");
    }
    
    function clearQueue()
    {
       $this->sendCommand("qclear"); 
    }
    
    function add($song)
    {
        $this->sendCommand("uadd " . $song->uri);
    }
    
    function addPlaylist($playlist, $song = null)
    {
        $this->api->createUserPlaylist([ "name" => $playlist->name, 
                                        "public" => false ]);
        // $create = "";
        // if ($song)
        // {
        //     $create = " " . $song->id;
        // }
        
        // $this->sendCommand("add " . $playlist->id . $create);
    }
    
    function playPlaylist($playlist, $song = null)
    {
        $create = "";
        if ($song)
        {
            $create = " " . $song->id;
        }
        
        $this->sendCommand("play " . $playlist->id . $create);
    }
    
    function getPlaylist($playlist)
    {
        return $this->parsePlaylistResponse($this->sendCommand("ls " . $playlist->id));
    }
    
    function getPlaylists()
    {
        if ($this->api->isAuthenticated())
        {
            return $this->parsePlaylistsResponse($this->api->getMyPlaylists());
        }
        return $this->parsePlaylistsResponse($this->sendCommand("ls"));
    }
    
    function rateUp($song)
    {
        
    }
    
    function rateDown($song)
    {
        
    }
    
    function removeQueue($song)
    {
        $this->sendCommand("qrm " . $song->queueNumber);
    }
    
    function removePlaylist($song)
    {
        
    }
    
    function openService()
    {
        return $this->querySpopDB('filepath', "SPOTIFY");
    }
}