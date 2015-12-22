<?php

namespace App\Http\Services;

use App\Http\Services\ConnectionService;

class MpdService
{
	protected $connectionService;
	
	public function __construct(ConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
    }
	
	// v2
	function openMpdSocket($host, $port) 
	{
		$sock = stream_socket_client('tcp://'.$host.':'.$port.'', $errorno, $errorstr, 30 );
		$response = $this->readMpdResponse($sock);
		
		if ($response = '') 
		{
			sysCmd('command/shell.sh '.$response);
			exit;
		}
        
		return $sock;
	}
	
	function closeMpdSocket($sock) 
	{
		$this->sendMpdCommand($sock,"close");
		fclose($sock);
	}
	
	// v2
	function sendMpdCommand($sock, $cmd) 
	{
		if ($cmd == 'cmediafix') 
		{
			$cmd = "pause\npause\n";
			fputs($sock, $cmd);
		} 
		else 
		{
			$cmd = $cmd."\n";
			fputs($sock, $cmd);	
		}
	}
	
	function chainMpdCommands($sock, $commands) 
	{
		foreach ($commands as $command) 
		{
			fputs($sock, $command."\n");
			fflush($sock);
			// MPD seems to be disoriented when it receives several commands chained. Need to sleep a little bit
			// 200 ms
			usleep(200000);
		}
	}
	
	// v3
	function readMpdResponse($sock) 
	{
		$output = "";
		while (!feof($sock)) 
		{
			$response =  fgets($sock, 1024);
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
	
	function loadAllLib($sock)
	{
		$flat = $this->_loadDirForLib($sock, array(), "");
		return json_encode($this->connectionService->_organizeJsonLib($flat));
	}
	
	function _loadDirForLib($sock, $flat, $dir) 
	{
		$this->sendMpdCommand($sock, "lsinfo \"".html_entity_decode($dir)."\"");
		$resp = $this->readMpdResponse($sock);
	
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
					$flat = $this->_loadDirForLib($sock, $flat, $value);
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
	
    function playAll($sock, $json) 
	{
		if (count($json) > 0) 
		{
			// Clear, add first file and play
			$commands = array();
			array_push($commands, "clear");
			array_push($commands, "add \"".html_entity_decode($json[0]['file'])."\"");
			array_push($commands, "play");
			$this->chainMpdCommands($sock, $commands);
	
			// Then add remaining
			$commands = array();
			for ($i = 1; $i < count($json); $i++) 
			{
				array_push($commands, "add \"".html_entity_decode($json[$i]['file'])."\"");
			}
			
			$this->chainMpdCommands($sock, $commands);
		}
	}
	
	function enqueueAll($sock, $json) 
	{
		$commands = array();
		foreach ($json as $song) 
		{
			$path = $song["file"];
			array_push($commands, "add \"".html_entity_decode($path)."\"");
		}
		
		$this->chainMpdCommands($sock, $commands);
	}
	
	// v2, Does not return until a change occurs.
	function sendMpdIdle($sock) 
	{
		$response = NULL;
	
		// Keep putting socket into "idle" mode until the response is something other than a mixer update
		// since we don't want to update the GUI for a volume change
		while (strcmp(substr($response, 0, 14), 'changed: mixer') == 0 || $response == NULL) 
		{
			$this->sendMpdCommand($sock,"idle");
			$response = $this->readMpdResponse($sock);
		}
	
		return true;
	}
	
	// Return state array for MPD. Does not return until a change occurs.
	function monitorMpdState($sock) 
	{
		if ($this->sendMpdIdle($sock)) 
		{
			return $this->MpdStatus($sock);
		}
	}
	
	// Ramplay functions
	function rp_checkPLid($id,$mpd) 
	{
		$_SESSION['DEBUG'] .= "rp_checkPLid:$id |";
		$this->sendMpdCommand($mpd,'playlistid '.$id);
		$response = $this->readMpdResponse($mpd);
		echo "<br>debug__".$response;
		echo "<br>debug__".stripos($response,'MPD error');
		
		if (!stripos($response,'OK')) 
		{
			return false;
		}
		
		return true;
	}
	
	//## unire con findPLposPath
	function rp_findPath($id,$mpd) 
	{
		//$_SESSION['DEBUG'] .= "rp_findPath:$id |";
		$idinfo = $this->sendMpdCommandWithResponse($mpd,'playlistid '.$id);
		$path = $idinfo[0]['file'];
		//$_SESSION['DEBUG'] .= "Path:$path |";
		return $path;
	}
	
	//## unire con rp_findPath()
	function findPLposPath($songpos,$mpd) 
	{
		//$_SESSION['DEBUG'] .= "rp_findPath:$id |";
		$idinfo = $this->sendMpdCommandWithResponse($mpd,'playlistinfo '.$songpos);
		$path = $idinfo[0]['file'];
		//$_SESSION['DEBUG'] .= "Path:$path |";
		return $path;
	}
	
	function rp_deleteFile($id,	$mpd) 
	{
		$_SESSION['DEBUG'] .= "rp_deleteFile:$id |";
		if (!unlink($this->rp_findPath($id,$mpd))) 
		{
			return false;
		}
		
		return true;
	}
	
	function rp_copyFile($id, $mpd) 
	{
		$_SESSION['DEBUG'] .= "rp_copyFile: $id|";
		$path = $this->rp_findPath($id,$mpd);
		$song = $this->parseFileStr($path,"/");
		$realpath = "/mnt/".$path;
		$ramplaypath = "/dev/shm/".$song;
		$_SESSION['DEBUG'] .= "rp_copyFilePATH: $path $ramplaypath|";
		
		if (copy($realpath, $ramplaypath)) 
		{
			$_SESSION['DEBUG'] .= "rp_addPlay:$id $song $path $pos|";
			return $path;
		}
		
		return false;
	}
	
	function rp_updateFolder($mpd) 
	{
		$_SESSION['DEBUG'] .= "rp_updateFolder: |";
		$this->sendMpdCommand($mpd,"update ramplay");
	}
	
	function rp_addPlay($path,$mpd,$pos) 
	{
		$song = $this->parseFileStr($path,"/");
		$ramplaypath = "ramplay/".$song;
		$_SESSION['DEBUG'] .= "rp_addPlay:$id $song $path $pos|";
		$this->addQueue($mpd,$ramplaypath);
		$this->sendMpdCommand($mpd,'play '.$pos);
	}
	
	function waitWorker($sleeptime,$section) 
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
					$mpd = $this->openMpdSocket('localhost', 6600);
					$this->sendMpdCommand($mpd,'update');
					$this->closeMpdSocket($mpd);
				break;
			}
		}
	} 
	
	function wrk_mpdconf($outpath,$db) 
	{
		// extract mpd.conf from SQLite datastore
		$dbh = $this->cfgdb_connect($db);
		$query_cfg = "SELECT param,value_player FROM cfg_mpd WHERE value_player!=''";
		$mpdcfg = $this->sdbquery($query_cfg,$dbh);
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
	
	function getTrackInfo($sock,$songID) 
	{
		// set currentsong, currentartis, currentalbum
		return $this->sendMpdCommandWithResponse($sock,"playlistinfo ".$songID);
	}
    
    function sendMpdCommandWithResponse($sock, $command)
    {
        $this->sendMpdCommand($sock, $command);
        $mpdResponse = $this->readMpdResponse($sock);
        
		return $this->connectionService->_parseFileListResponse($mpdResponse);
    }
	
	// TODO Justus check which one to get?
	function getPlayQueue($sock) 
	{
		return $this->sendMpdCommandWithResponse($sock, "playlistinfo");
	}
	
	function searchDB($sock, $querytype, $query = null) 
	{
        $response = "";
        
		switch ($querytype) 
		{
			case "filepath":
				if (isset($query) && !empty($query))
				{
					$response = $this->sendMpdCommandWithResponse($sock,"lsinfo \"".html_entity_decode($query)."\"");
					break;
				} 
				else 
				{
					$response = $this->sendMpdCommandWithResponse($sock,"lsinfo");
					break;
				}
			case "album":
			case "artist":
			case "title":
			case "file":
				$response = $this->sendMpdCommandWithResponse($sock,"search ".$querytype." \"".html_entity_decode($query)."\"");
				//$this->sendMpdCommand($sock,"search any \"".html_entity_decode($query)."\"");
			break;
		}
		
		//$response =  htmlentities($this->readMpdResponse($sock),ENT_XML1,'UTF-8');
		//$response = htmlspecialchars($this->readMpdResponse($sock));
		return $response;
	}
	
	function remTrackQueue($sock,$songpos) 
	{
		$datapath = $this->findPLposPath($songpos,$sock);
		$this->sendMpdCommand($sock,"delete ".$songpos);
	
		return $this->readMpdResponse($sock);
	}
	
	function addQueue($sock,$path) 
	{
		$fileext = $this->parseFileStr($path,'.');
	
		if ($fileext == 'm3u' OR $fileext == 'pls' OR strpos($path, '/') === false) 
		{
			$this->sendMpdCommand($sock,"load \"".html_entity_decode($path)."\"");
		} 
		else 
		{
			$this->sendMpdCommand($sock,"add \"".html_entity_decode($path)."\"");
		}
	
		return $this->readMpdResponse($sock);
	}
	
	function MpdStatus($sock) 
	{
		return $this->sendMpdCommandWithResponse($sock, "status");
	}
}