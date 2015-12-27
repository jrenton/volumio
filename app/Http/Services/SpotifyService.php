<?php

namespace App\Http\Services;

use App\Http\Services\ConnectionService;
use App\Http\Songs\SpotifySong;

class SpotifyService implements IMusicPlayerService
{
	private $connectionService;
    private $spop;
	
	public function __construct(ConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
        $this->spop = $this->openSpopSocket(DAEMONIP, 6602);
    }

	// Spotify daemon communication functions
	function openSpopSocket($host, $portSpop) 
	{
		$sock = stream_socket_client('tcp://'.$host.':'.$portSpop.'', $errorno, $errorstr, 30 );
	
		if ($sock) 
		{
			// First response is typically "spop [version]"
			$response = fgets($sock);
		}
	
		return $sock;
	}
	
	function closeSpopSocket() 
	{
		$this->sendCommand("bye");
		fclose($this->spop);
	}
	
	function sendCommand($cmd) 
	{
		if ($this->spop) 
		{
			$cmd = $cmd."\n";
			fputs($this->spop, $cmd);
	
			while(!feof($this->spop))
			{
				// fgets() may time out during the wait for response from commands like 'idle'.
				// This loop will keep reading until a response is received, or until the socket closes.
				$output = fgets($this->spop);
	
				if ($output) 
				{
					break;
				}
			}
	
			return $this->_parseSpopResponse($output);
		}
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
	
		if (array_key_exists("title", $arrayResponse) == TRUE) 
		{
			$arrayReturn["currentsong"] = $arrayResponse["title"];
		}
	
		if (array_key_exists("artist", $arrayResponse) == TRUE) 
		{
			$arrayReturn["currentartist"] = $arrayResponse["artist"];
		}
	
		if (array_key_exists("album", $arrayResponse) == TRUE) 
		{
			$arrayReturn["currentalbum"] = $arrayResponse["album"];
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
			return $this->_searchSpopTracks($queryString);
		}
	
		return array();
	}
	
	function _parseSpopResponse($resp) 
	{
		return json_decode($resp, true);
	}
	
	// Perform a Spotify search
	function _searchSpopTracks($queryString) 
	{
		$arrayReturn = array();
		$arrayResponse = $this->sendCommand("search \"" . $queryString . "\"");
	
		$i = 0;
		$nItems = sizeof($arrayResponse["tracks"]);
		while ($i < $nItems) 
        {
			$arrayCurrentEntry = array();
			$arrayCurrentEntry["Type"] = "SpopTrack";
			$arrayCurrentEntry["ServiceType"] = "Spotify";
			$arrayCurrentEntry["SpopTrackUri"] = (string)$arrayResponse["tracks"][$i]["uri"];
			$arrayCurrentEntry["Title"] = $arrayResponse["tracks"][$i]["title"];
			$arrayCurrentEntry["Artist"] = $arrayResponse["tracks"][$i]["artist"];
			$arrayCurrentEntry["Album"] = $arrayResponse["tracks"][$i]["album"];
	
			array_push($arrayReturn, $arrayCurrentEntry);
	
			$i++;
		}
	
		return $arrayReturn;
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
			$arrayRootItem["Type"] = "SpopDirectory";
			$arrayRootItem["ServiceType"] = "Spotify";
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
			    // This is a tracklist within a playlist
				$nItems = sizeof($arrayResponse["tracks"]);
				while ($i < $nItems) 
                {
					$arrayCurrentEntry = array();
					$arrayCurrentEntry["Type"] = "SpopTrack";
					$arrayCurrentEntry["ServiceType"] = "Spotify";
					$arrayCurrentEntry["SpopTrackUri"] = (string)$arrayResponse["tracks"][$i]["uri"];
					$arrayCurrentEntry["Title"] = $arrayResponse["tracks"][$i]["title"];
					$arrayCurrentEntry["Artist"] = $arrayResponse["tracks"][$i]["artist"];
					$arrayCurrentEntry["Album"] = $arrayResponse["tracks"][$i]["album"];
					
					array_push($arrayReturn, $arrayCurrentEntry);
	
					$i++;
				}
			} 
            else if (isset($arrayResponse["playlists"])) 
            {
			    // This is a browsable listing
				$nItems = sizeof($arrayResponse["playlists"]);
				while ($i < $nItems) 
                {
					$arrayCurrentEntry = array();
					$arrayCurrentEntry["Type"] = "SpopDirectory";
					$arrayCurrentEntry["ServiceType"] = "Spotify";
					$sItemDisplayName = $arrayResponse["playlists"][$i]["name"];
	
					if (strcmp($arrayResponse["playlists"][$i]["type"], "playlist") == 0) 
                    {
					   // This is a browsable playlist
						$arrayCurrentEntry["SpopPlaylistIndex"] = $arrayResponse["playlists"][$i]["index"];
						$sItemDirectory = $sCurrentDirectory . "/" . $i . "@" . $arrayResponse["playlists"][$i]["index"];
	
						if ($arrayResponse["playlists"][$i]["index"] == 0) 
                        {
							$sItemDisplayName = "Starred";
						}
					} 
                    else 
                    {
					   // This is a Spotify folder
						$sItemDirectory = $sCurrentDirectory . "/" . $i;
					}
	
					$arrayCurrentEntry["directory"] = $sItemDirectory;
					$arrayCurrentEntry["DisplayName"] = $sItemDisplayName;
					array_push($arrayReturn, $arrayCurrentEntry);
	
					$i++;
				}
			}
		}
	
		return $arrayReturn;
	}
    
    function play($song = null)
    {
        if (!$song)
        {
            $this->sendCommand("play");
        }
        else if ($song->spopTrackUri)
        {
            $this->sendCommand("uplay " . $song->spopTrackUri);
        }
    }
    
    function stop()
    {
        $this->sendCommand("stop");
    }
    
    function pause()
    {
        $this->sendCommand("pause");
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
        return $this->sendCommand("status");
    }
    
    function image($song = null)
    {
        if (!$song)
        {
            return $this->sendCommand("image");
        }
        
        return $this->sendCommand("uimage " . $song->spopTrackUri);
    }
    
    function repeat()
    {
        $tnis->sendCommand("repeat");
    }
    
    function shuffle()
    {
        $tnis->sendCommand("shuffle");
    }
    
    function search($query)
    {
        return $this->sendCommand("search " . $query);
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
        $this->sendCommand("uadd " . $song->spopTrackUri);
    }
    
    function addPlaylist($playlist, $song = null)
    {
        $create = "";
        if ($song)
        {
            $create = " " . $song->id;
        }
        
        $this->sendCommand("add " . $playlist->id . $create);
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
        return $this->sendCommand("ls " . $playlist->id);
    }
    
    function getPlaylists()
    {
        return $this->sendCommand("ls");
    }
    
    function rateUp($song)
    {
        
    }
    
    function rateDown($song)
    {
        
    }
}