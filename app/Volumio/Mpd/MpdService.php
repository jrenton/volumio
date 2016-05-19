<?php

namespace App\Volumio\Mpd;

use App\Volumio\Services\ConnectionService;
use App\Volumio\Services\IMusicPlayerService;
use App\Volumio\Notifiers\SongChangeNotifier;

class MpdService implements IMusicPlayerService
{
	protected $connectionService;
	protected $songChangeNotifier;
    protected $sock;
	
	public function __construct(ConnectionService $connectionService, SongChangeNotifier $songChangeNotifier)
    {
        if (!$connectionService)
        {
            $connectionService = new ConnectionService;
        }
        
        if(!$songChangeNotifier)
        {
            $songChangeNotifier = new SongChangeNotifier;
        }
        $this->connectionService = $connectionService;
        $this->songChangeNotifier = $songChangeNotifier;

        $this->sock = $this->openMpdSocket("localhost", 6600);;
    }
	
	// v2
	function openMpdSocket($host, $port) 
	{
		$this->sock = stream_socket_client('tcp://'.$host.':'.$port.'', $errorno, $errorstr, 30 );
		$response = $this->readMpdResponse();
		
		if ($response = '') 
		{
			sysCmd('command/shell.sh '.$response);
			exit;
		}
        
		return $this->sock;
	}
	
	function closeMpdSocket() 
	{
		$this->sendMpdCommand("close");
		fclose($this->sock);
	}
	
	// v2
	function sendMpdCommand($cmd) 
	{
		if ($cmd == 'cmediafix') 
		{
			$cmd = "pause\npause\n";
			fputs($this->sock, $cmd);
		} 
		else 
		{
			$cmd = $cmd."\n";
			fputs($this->sock, $cmd);	
		}
	}
	
	function chainMpdCommands($commands) 
	{
		foreach ($commands as $command) 
		{
			fputs($this->sock, $command."\n");
			fflush($this->sock);
			// MPD seems to be disoriented when it receives several commands chained. Need to sleep a little bit
			// 200 ms
			usleep(200000);
		}
	}
	
	// v3
	function readMpdResponse() 
	{
		$output = "";
		while (!feof($this->sock)) 
		{
			$response =  fgets($this->sock, 1024);
			$output .= $response;
			
			if (strncmp(MPD_RESPONSE_OK, $response, strlen(MPD_RESPONSE_OK)) == 0) 
			{
				break;
			}
			
			if (strncmp(MPD_RESPONSE_ERR, $response, strlen(MPD_RESPONSE_ERR)) == 0) 
			{
				$output = "MPD error: $response";
				break;
			}
		}
		
		return $output;
	}
    
    // format Output for "playlist"
	function parseFileListResponse($resp) 
	{
		if ( is_null($resp) ) 
		{
			return NULL;
		}
	
		$plistArray = array();
		$dirArray = array();
		$plCounter = -1;
		$dirCounter = 0;
		$plistLine = strtok($resp, "\n");
		$plistFile = "";
	
        while ( $plistLine ) 
        {
            try
            {
                list ( $element, $value ) = explode(": ", $plistLine, 2);
	
                if ( $element == "file" OR $element == "playlist") 
                {
                    $plCounter++;
                    $plistFile = $value;
                    $plistArray[$plCounter]["file"] = $plistFile;
                    $plistArray[$plCounter]["fileext"] = $this->connectionService->parseFileStr($plistFile,'.');
                    $plistArray[$plCounter]["type"] = "MpdFile";
                    $plistArray[$plCounter]["serviceType"] = "Mpd";
                } 
                else if ( $element == "directory") 
                {
                    $dirCounter++;
                    $dirArray[$dirCounter]["directory"] = $value;
                    $dirArray[$dirCounter]["type"] = "Directory";
                    $dirArray[$plCounter]["serviceType"] = "Mpd";
                } 
                else 
                {
                    $plistArray[$plCounter][$element] = $value;
                    
                    if(isset($plistArray[$plCounter]["Time"]))
                    {
                        $plistArray[$plCounter]["Time2"] = $this->connectionService->songTime($plistArray[$plCounter]["Time"]);					
                    }
                }
            }
            catch(\Exception $ex)
            {
                // If a line does not have a ":", it errors,
                //  however, if you check for the existence
                //  of one, it times out.
            }
			
			$plistLine = strtok("\n");
		}
        
		return array_merge($dirArray, $plistArray);
	}
	
	function loadAllLib()
	{
		$flat = $this->_loadDirForLib(array(), "");
		return json_encode($this->connectionService->_organizeJsonLib($flat));
	}
	
	function _loadDirForLib($flat, $dir) 
	{
		$this->sendMpdCommand("lsinfo \"" . html_entity_decode($dir) . "\"");
		$resp = $this->readMpdResponse();
	
		if (!is_null($resp)) 
		{
			$lines = explode("\n", $resp);
			$iItem = 0;
			$skip = true;
			for ($iLine = 0; $iLine < count($lines); $iLine++) 
			{
				list($element, $value) = explode(": ", $lines[$iLine], 2);
				if ($element == "file") 
				{
					$skip = false;
					$iItem = count($flat);
				} 
				else if ($element == "directory") 
				{
					$flat = $this->_loadDirForLib($flat, $value);
					$skip = true;
				} 
				else if ($element == "playlist") 
				{
					$skip = true;
				}
				
				if (!$skip) 
				{
					$flat[$iItem][$element] = $value;
				}
			} 
		}
		
		return $flat;
	}
	
    function playAll($json) 
	{
		if (count($json) > 0) 
		{
			// Clear, add first file and play
			$commands = array();
			array_push($commands, "clear");
			array_push($commands, "add \"".html_entity_decode($json[0]['file'])."\"");
			array_push($commands, "play");
			$this->chainMpdCommands($commands);
	
			// Then add remaining
			$commands = array();
			for ($i = 1; $i < count($json); $i++) 
			{
				array_push($commands, "add \"".html_entity_decode($json[$i]['file'])."\"");
			}
			
			$this->chainMpdCommands($commands);
		}
	}
	
	function enqueueAll($json) 
	{
		$commands = array();
		foreach ($json as $song) 
		{
			$path = $song["file"];
			array_push($commands, "add \"".html_entity_decode($path)."\"");
		}
		
		$this->chainMpdCommands($commands);
	}
	
	// v2, Does not return until a change occurs.
	function sendMpdIdle() 
	{
		$response = NULL;
	
		// Keep putting socket into "idle" mode until the response is something other than a mixer update
		// since we don't want to update the GUI for a volume change
		while (strcmp(substr($response, 0, 14), 'changed: mixer') == 0 || $response == NULL) 
		{
			$this->sendMpdCommand("idle");
			$response = $this->readMpdResponse();
		}
	
		return true;
	}
	
	// Return state array for MPD. Does not return until a change occurs.
	function monitorMpdState() 
	{
		if ($this->sendMpdIdle()) 
		{
			return $this->MpdStatus();
		}
	}
	
	// Ramplay functions
	function rp_checkPLid($id) 
	{
		$_SESSION['DEBUG'] .= "rp_checkPLid:$id |";
		$this->sendMpdCommand('playlistid '.$id);
		$response = $this->readMpdResponse();
		echo "<br>debug__".$response;
		echo "<br>debug__".stripos($response,'MPD error');
		
		if (!stripos($response, 'OK')) 
		{
			return false;
		}
		
		return true;
	}
	
	//## unire con findPLposPath
	function rp_findPath($id) 
	{
		//$_SESSION['DEBUG'] .= "rp_findPath:$id |";
		$idinfo = $this->sendMpdCommandWithResponse('playlistid ' . $id);
		$path = $idinfo[0]['file'];
		//$_SESSION['DEBUG'] .= "Path:$path |";
		return $path;
	}
	
	//## unire con rp_findPath()
	function findPLposPath($songpos) 
	{
		//$_SESSION['DEBUG'] .= "rp_findPath:$id |";
		$idinfo = $this->sendMpdCommandWithResponse('playlistinfo ' . $songpos);
		$path = $idinfo[0]['file'];
		//$_SESSION['DEBUG'] .= "Path:$path |";
		return $path;
	}
	
	function rp_deleteFile($id) 
	{
		$_SESSION['DEBUG'] .= "rp_deleteFile:$id |";
		if (!unlink($this->rp_findPath($id))) 
		{
			return false;
		}
		
		return true;
	}
	
	function rp_copyFile($id) 
	{
		$_SESSION['DEBUG'] .= "rp_copyFile: $id|";
		$path = $this->rp_findPath($id);
		$song = $this->parseFileStr($path, "/");
		$realpath = "/mnt/" . $path;
		$ramplaypath = "/dev/shm/" . $song;
		$_SESSION['DEBUG'] .= "rp_copyFilePATH: $path $ramplaypath|";
		
		if (copy($realpath, $ramplaypath)) 
		{
			$_SESSION['DEBUG'] .= "rp_addPlay:$id $song $path $pos|";
			return $path;
		}
		
		return false;
	}
	
	function rp_updateFolder() 
	{
		$_SESSION['DEBUG'] .= "rp_updateFolder: |";
		$this->sendMpdCommand("update ramplay");
	}
	
	function rp_addPlay($path, $pos) 
	{
		$song = $this->parseFileStr($path,"/");
		$ramplaypath = "ramplay/" . $song;
		$_SESSION['DEBUG'] .= "rp_addPlay:$id $song $path $pos|";
		$this->addQueue($ramplaypath);
		$this->sendMpdCommand('play ' . $pos);
	}
	
	function waitWorker($sleeptime, $section) 
	{
		if ($_SESSION['w_active'] == 1) 
		{
			do 
			{
				sleep($sleeptime);
				session_start();
				session_write_close();
			} 
			while ($_SESSION['w_active'] != 0);
	
			switch ($section) 
			{
				case 'sources':
                
					//$mpd = $this->openMpdSocket('localhost', 6600);
					$this->sendMpdCommand('update');
					//$this->closeMpdSocket($mpd);
				break;
			}
		}
	} 
	
	function wrk_mpdconf($outpath,$db) 
	{
		// extract mpd.conf from SQLite datastore
		$dbh = $this->cfgdb_connect($db);
		$query_cfg = "SELECT param,value_player FROM cfg_mpd WHERE value_player!=''";
		$mpdcfg = $this->sdbquery($query_cfg, $dbh);
		$dbh = null;
	
		// set mpd.conf file header
		$output = "###################################\n";
		$output .= "# Auto generated mpd.conf file\n";
		$output .= "# please DO NOT edit it manually!\n";
		$output .= "# Use player-UI MPD config section\n";
		$output .= "###################################\n";
		$output .= "\n";
	
		// parse DB output
		foreach ($mpdcfg as $cfg) 
		{
			if ($cfg['param'] == 'audio_output_format' && $cfg['value_player'] == 'disabled')
			{
				$output .= '';
			} 
			else if ($cfg['param'] == 'dop') 
			{
				$dop = $cfg['value_player'];
			} 
			else if ($cfg['param'] == 'device') 
			{
				$device = $cfg['value_player'];
				var_export($device);
				// $output .= '';
			} 
			else if ($cfg['param'] == 'mixer_type' && $cfg['value_player'] == 'hardware' ) 
			{ 
				// $hwmixer['device'] = 'hw:0';
				$hwmixer['control'] = $this->connectionService->alsa_findHwMixerControl($device);
				// $hwmixer['index'] = '1';
			}  
			else 
			{
				$output .= $cfg['param']." \t\"".$cfg['value_player']."\"\n";
			}
		}
	
		// format audio input / output interfaces
		$output .= "max_connections \"20\"\n";
		$output .= "\n";
		$output .= "decoder {\n";
		$output .= "\t\tplugin \"ffmpeg\"\n";
		$output .= "\t\tenabled \"yes\"\n";
		$output .= "}\n";
		$output .= "\n";
		$output .= "input {\n";
		$output .= "\t\tplugin \"curl\"\n";
		$output .= "}\n";
		$output .= "\n";
		$output .= "audio_output {\n\n";
		$output .= "\t\t type \t\t\"alsa\"\n";
		$output .= "\t\t name \t\t\"Output\"\n";
		$output .= "\t\t device \t\"hw:".$device.",0\"\n";
		if (isset($hwmixer)) 
		{
			//$output .= "\t\t mixer_device \t\"".$hwmixer['device']."\"\n";
			$output .= "\t\t mixer_control \t\"".$hwmixer['control']."\"\n";
			$output .= "\t\t mixer_device \t\"hw:".$device."\"\n";
			$output .= "\t\t mixer_index \t\"0\"\n";
			//$output .= "\t\t mixer_index \t\"".$hwmixer['index']."\"\n";
		}
		$output .= "\t\t dop \t\"".$dop."\"\n";
		$output .= "\n}\n";
	
		// write mpd.conf file
		$fh = fopen($outpath."/mpd.conf", 'w');
		fwrite($fh, $output);
		fclose($fh);
	}
	
	function getTrackInfo($songID) 
	{
		// set currentsong, currentartist, currentalbum
		return $this->sendMpdCommandWithResponse("playlistinfo " . $songID);
	}
    
    function sendMpdCommandWithResponse($command)
    {
        $this->sendMpdCommand($command);
        $mpdResponse = $this->readMpdResponse();
        
		return $this->parseFileListResponse($mpdResponse);
    }
	
	// TODO Justus check which one to get?
	function getPlayQueue() 
	{
		return $this->sendMpdCommandWithResponse("playlistinfo");
	}
	
	function searchDB($querytype, $query = null) 
	{
        $response = "";
        
		switch ($querytype) 
		{
			case "filepath":
				if (isset($query) && !empty($query))
				{
					$response = $this->sendMpdCommandWithResponse("lsinfo \"".html_entity_decode($query)."\"");
					break;
				} 
				else 
				{
					$response = $this->sendMpdCommandWithResponse("lsinfo");
					break;
				}
			case "album":
			case "artist":
			case "title":
			case "file":
				$response = $this->sendMpdCommandWithResponse("search ".$querytype." \"".html_entity_decode($query)."\"");
				//$this->sendMpdCommand($this->sock,"search any \"".html_entity_decode($query)."\"");
			break;
		}
		
		//$response =  htmlentities($this->readMpdResponse($this->sock),ENT_XML1,'UTF-8');
		//$response = htmlspecialchars($this->readMpdResponse($this->sock));
		return $response;
	}
	
	function remTrackQueue($songpos) 
	{
		//$datapath = $this->findPLposPath($songpos);
		$this->sendMpdCommand("delete " . $songpos);
	
		return $this->readMpdResponse();
	}
	
	function addQueue($path) 
	{
		$fileext = $this->parseFileStr($path,'.');
	
		if ($fileext == 'm3u' OR $fileext == 'pls' OR strpos($path, '/') === false) 
		{
			$this->sendMpdCommand("load \"".html_entity_decode($path)."\"");
		} 
		else 
		{
			$this->sendMpdCommand("add \"".html_entity_decode($path)."\"");
		}
	
		return $this->readMpdResponse();
	}
	
	function MpdStatus() 
	{
		return $this->sendMpdCommandWithResponse("status");
	}
    
    function play($song = null)
    {
        
    }
    
    function stop()
    {
        
    }
    
    function pause()
    {
        
    }
    
    function next()
    {
        
    }
    
    function previous()
    {
        
    }
    
    function status()
    {
        
    }
    
    function image($song = null)
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
        
    }
    
    function getQueue()
    {
        
    }
    
    function clearQueue()
    {
        
    }
    
    function playPlaylist($playlist, $song = null)
    {
        
    }
    
    function add($song)
    {
        
    }
    
    function addPlaylist($playlist, $song = null)
    {
        
    }
    
    function getPlaylist($playlist)
    {
        
    }
    
    function getPlaylists()
    {
        
    }
    
    function rateUp($song)
    {
        
    }
    
    function rateDown($song)
    {
        
    }
    
    function removeQueue($song)
    {
        
    }
    
    function removePlaylist($song)
    {
        
    }
    
    function openService()
    {
        return $this->searchDB('filepath', "MPD");
    }
}