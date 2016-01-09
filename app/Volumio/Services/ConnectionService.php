<?php

namespace App\Volumio\Services;

class ConnectionService
{
	function _organizeJsonLib($flat) 
	{
		// Build json like "{Genre1: {Artist1: {Album1: [{song1}, {song2}], Album2:...}, Artist2:...}, Genre2:...}
		$lib = array();
		foreach ($flat as $songData) 
		{
			$genre = $songData["Genre"] ? $songData["Genre"] : "Unknown";
			$artist = $songData["AlbumArtist"] ? $songData["AlbumArtist"] : ($songData["artist"] ? $songData["artist"] : "Unknown");
			$album = $songData["album"] ? $songData["album"] : "Unknown";
	
			if (!$lib[$genre]) 
			{
				$lib[$genre] = array();
			}
			
			if (!$lib[$genre][$artist]) 
			{
				$lib[$genre][$artist] = array();
			}
			
			if (!$lib[$genre][$artist][$album]) 
			{
				$lib[$genre][$artist][$album] = array();
			}
			
			$songDataLight = array(	"file" => $songData['file'],
						"display" => ($songData['Track'] ? $songData['Track']." - " : "")
							.$songData['title']);
							
			array_push($lib[$genre][$artist][$album], $songDataLight);
		}
		
		return $lib;
	}
	
	function getTemplate($template) 
	{
		return str_replace("\"","\\\"",implode("",file($template)));
	}
	
	function echoTemplate($template) 
	{
		echo $template;
	}
	
	// create JS like Timestamp
	function jsTimestamp() 
	{
		return round(microtime(true) * 1000);
	}
	
	function songTime($sec) 
	{
		$minutes = sprintf('%02d', floor($sec / 60));
		$seconds = sprintf(':%02d', (int) $sec % 60);
		return $minutes.$seconds;
	}
	
	function phpVer() 
	{
		$version = phpversion();
		return substr($version, 0, 3); 
	}
	
	function sysCmd($syscmd) 
	{
		exec($syscmd." 2>&1", $output);
		return $output;
	}
    
    function convertTimeToSeconds($time)
    {
        $times = explode(":", $time);
        
        if (!is_array($times))
        {
            return 0;
        }
        
        if (!array_key_exists(0, $times) || !array_key_exists(1, $times))
        {
            return 0;
        }

        return ((int)$times[0] * 60) + (int)$times[1];
    }
	
	// format Output for "status"
	function _parseStatusResponse($resp) 
	{
		if ( is_null($resp) ) 
		{
			return NULL;
		}
		
		$plistArray = array();
		$plistLine = strtok($resp,"\n");
		$plistFile = "";
		$plCounter = -1;
        
		while ($plistLine) 
		{
            try
            {
                list ( $element, $value ) = explode(": ", $plistLine, 2);
                $plistArray[$element] = $value;
            }
            catch(\Exception $ex)
            {
                
            }
            
            $plistLine = strtok("\n");            
		}

        $percent = 0;
        $elapsed = 0;
        $totalTime = 0;
        
		// "elapsed time song_percent" added to output array
        if (array_key_exists("time", $plistArray))
        {
            $time = explode(":", $plistArray['time']); 
             
            if ($time[0] != 0) 
            {
                $percent = round(($time[0]*100)/$time[1]);
                $elapsed = $time[0];
                $totalTime = $time[1];
            }          
        }
        
        $plistArray["song_percent"] = $percent;
        $plistArray["elapsed"] = $elapsed;
        $plistArray["time"] = $totalTime;

        $audioSampleRate = 0;
        $audioSampleDepth = 0;
        $audioChannels = 0;
        
        if (array_key_exists("audio", $plistArray))
        {
            // "audio format" output
            $audio_format = explode(":", $plistArray['audio']);
            switch ($audio_format[0]) 
            {
                case '48000':
                case '96000':
                case '192000':
                    $audioSampleRate = rtrim(rtrim(number_format($audio_format[0]),0),',');
                break;
                case '44100':
                case '88200':
                case '176400':
                case '352800':
                    $audioSampleRate = rtrim(number_format($audio_format[0],0,',','.'),0);
                break;
            }
            
            // format "audio_sample_depth" string
            $audioSampleDepth = $audio_format[1];

            // format "audio_channels" string
            if ($audio_format[2] == "2") $audioChannels = "Stereo";
            if ($audio_format[2] == "1") $audioChannels = "Mono";
            if ($audio_format[2] > 2) $audioChannels = "Multichannel";  
        }

        $plistArray['audio_sample_rate'] = $audioSampleRate;
        $plistArray['audio_sample_depth'] = $audioSampleDepth;
        $plistArray['audio_channels'] = $audioChannels;
        
		return $plistArray;
	}
	
	// get file extension
	function parseFileStr($strFile, $delimiter) 
	{
		$pos = strrpos($strFile, $delimiter);
		$str = substr($strFile, $pos+1);
		return $str;
	}
	
	// cfg engine and session management
	function playerSession($action, $db, $var = null, $value = null) 
	{
		$status = session_status();
	
		// open new PHP SESSION
		if ($action == 'open') 
		{
			// check the PHP SESSION status
			if($status != 2) 
			{
				// check presence of sessionID into SQLite datastore
				//debug
				// echo "<br>---------- READ SESSION -------------<br>";
				$sessionid = $this->playerSession('getsessionid', $db);
				if (!empty($sessionid)) 
				{
					// echo "<br>---------- SET SESSION ID-------------<br>";
					session_id($sessionid);
					session_start();
				} 
				else 
				{
					session_start();
					// echo "<br>---------- STORE SESSION -------------<br>";
					$this->playerSession('storesessionid',$db);
				}
			}
			$dbh  = $this->cfgdb_connect($db);
			// scan cfg_engine and store values in the new session
			$params = $this->cfgdb_read('cfg_engine',$dbh);
			foreach ($params as $row) 
			{
				$_SESSION[$row['param']] = $row['value'];
			}
			//debug
			//print_r($_SESSION);
		// close SQLite handle
		$dbh  = null;
		}
	
		// unlock PHP SESSION file
		if ($action == 'unlock') 
		{
			session_write_close();
			// if (session_write_close()) {
				// return true;
			// }
		}
		
		// unset and destroy current PHP SESSION
		if ($action == 'destroy') 
		{
			session_unset();
			if (session_destroy()) 
			{
				$dbh  = $this->cfgdb_connect($db);
				if ($this->cfgdb_update('cfg_engine',$dbh,'sessionid','')) 
				{
					$dbh = null;
					return true;
				}
				
				echo "cannot reset session on SQLite datastore";
				return false;
			}
		}
		
		// store a value in the cfgdb and in current PHP SESSION
		if ($action == 'write') 
		{
			$_SESSION[$var] = $value;
			$dbh  = $this->cfgdb_connect($db);
			$this->cfgdb_update('cfg_engine',$dbh,$var,$value);
			$dbh = null;
		}
		
		// record actual PHP Session ID in SQLite datastore
		if ($action == 'storesessionid') 
		{
			$sessionid = session_id();
			$this->playerSession('write',$db,'sessionid',$sessionid);
		}
		
		// read PHP SESSION ID stored in SQLite datastore and use it to "attatch" the same SESSION (used in worker)
		if ($action == 'getsessionid') 
		{
			$dbh  = $this->cfgdb_connect($db);
			$result = $this->cfgdb_read('cfg_engine', $dbh, 'sessionid');
			$dbh = null;
			return $result['0']['value'];
		}
	}
	
	function cfgdb_connect($dbpath) 
	{
		if ($dbh = new \PDO($dbpath)) 
		{
			return $dbh;
		}
		
		echo "cannot open the database";
		
		return false;
	}
	
	function cfgdb_read($table, $dbh, $param = null, $id = null) 
	{
		if(!isset($param)) 
		{
			$querystr = 'SELECT * from '.$table;
		} 
		else if (isset($id)) 
		{
			$querystr = "SELECT * from ".$table." WHERE id='".$id."'";
		} 
		else if ($param == 'mpdconf')
		{
			$querystr = "SELECT param,value_player FROM cfg_mpd WHERE value_player!=''";
		} 
		else if ($param == 'mpdconfdefault') 
		{
			$querystr = "SELECT param,value_default FROM cfg_mpd WHERE value_default!=''";
		} 
		else 
		{
			$querystr = 'SELECT value from '.$table.' WHERE param="'.$param.'"';
		}
		
		$result = $this->sdbquery($querystr,$dbh);
		
		return $result;
	}
	
	function cfgdb_update($table,$dbh,$key,$value) 
    {
		switch ($table) 
        {
			case 'cfg_engine':
				$querystr = "UPDATE ".$table." SET value='".$value."' where param='".$key."'";
				break;
			case 'cfg_lan':
				$querystr = "UPDATE ".$table." SET dhcp='".$value['dhcp']."', ip='".$value['ip']."', netmask='".$value['netmask']."', gw='".$value['gw']."', dns1='".$value['dns1']."', dns2='".$value['dns2']."' where name='".$value['name']."'";
				break;
			case 'cfg_mpd':
				$querystr = "UPDATE ".$table." set value_player='".$value."' where param='".$key."'";
				break;
			case 'cfg_wifisec':
				$querystr = "UPDATE ".$table." SET ssid='".$value['ssid']."', security='".$value['encryption']."', password='".$value['password']."' where id=1";
				break;
			case 'cfg_source':
				$querystr = "UPDATE ".$table." SET name='".$value['name']."', type='".$value['type']."', address='".$value['address']."', remotedir='".$value['remotedir']."', username='".$value['username']."', password='".$value['password']."', charset='".$value['charset']."', rsize='".$value['rsize']."', wsize='".$value['wsize']."', options='".$value['options']."', error='".$value['error']."' where id=".$value['id'];
				break;
		}
		//debug
		error_log(">>>>> cfgdb_update(".$table.",dbh,".$key.",".$value.") >>>>> \n".$querystr, 0);
	
		if ($this->sdbquery($querystr,$dbh)) 
        {
			return true;
		}
        
        return false;
	}
	
	function cfgdb_write($table,$dbh,$values)
    {
		$querystr = "INSERT INTO ".$table." VALUES (NULL, ".$values.")";
		//debug
		error_log(">>>>> cfgdb_write(".$table.",dbh,".$values.") >>>>> \n".$querystr, 0);
	
		if ($this->sdbquery($querystr,$dbh)) 
        {
			return true;
		}
        
        return false;
	}
	
	function cfgdb_delete($table,$dbh,$id) 
    {
        if (!isset($id)) 
        {
            $querystr = "DELETE FROM ".$table;
        } 
        else 
        {
            $querystr = "DELETE FROM ".$table." WHERE id=".$id;
        }
        
        //debug
        error_log(">>>>> cfgdb_delete(".$table.",dbh,".$id.") >>>>> \n".$querystr, 0);
		if ($this->sdbquery($querystr, $dbh)) 
        {
		  return true;
		}
        
		return false;
	}
	
	function sdbquery($querystr, $dbh) 
	{
		$query = $dbh->prepare($querystr);
		if ($query->execute()) 
		{
			$result = array();
			$i = 0;
			foreach ($query as $value) 
			{
				$result[$i] = $value;
				$i++;
			}
			$dbh = null;
			
			if (empty($result)) 
			{
				return true;
			}
			
			return $result;
		}
		
		return false;
	}
	
	function rp_clean() 
	{
		$_SESSION['DEBUG'] .= "rp_clean: |";
		recursiveDelete('/dev/shm/');
	}
	
	function recursiveDelete($str)
	{
		if(is_file($str))
		{
			return @unlink($str);
			// aggiungere ricerca path in playlist e conseguente remove from playlist
		}
		else if(is_dir($str))
		{
			$scan = glob(rtrim($str,'/').'/*');
			foreach($scan as $index => $path)
			{
				recursiveDelete($path);
			}
		}
	}
	
	function pushFile($filepath) 
	{
		if (file_exists($filepath)) 
		{
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.basename($filepath));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($filepath));
			ob_clean();
			flush();
			readfile($filepath);
			
			return true;
		}
		
		return false;
	}
	
	// check if mpd.conf or interfaces was modified outside
	function hashCFG($action,$db) 
	{
		$this->playerSession('open',$db);
		
		switch ($action) 
		{	
			// case 'check_net':
			// $hash = md5_file('/etc/network/interfaces');
			// if ($hash != $_SESSION['netconfhash']) {
			// 	if ($_SESSION['netconf_advanced'] != 1) {
			// 	$this->playerSession('write',$db,'netconf_advanced',1); 
			// 	}
			// return false;
			// } else {
			// 	if ($_SESSION['netconf_advanced'] != 0) {
			// 	$this->playerSession('write',$db,'netconf_advanced',0);
			// 	}
			// }
			// break;
			
			// case 'check_mpd':
			// $hash = md5_file('/etc/mpd.conf');
			// if ($hash != $_SESSION['mpdconfhash']) {
			// 	if ($_SESSION['mpdconf_advanced'] != 1) {
			// 	$this->playerSession('write',$db,'mpdconf_advanced',1); 
			// 	}
			// return false;
			// } else {
			// 	if ($_SESSION['mpdconf_advanced'] != 0) {
			// 	$this->playerSession('write',$db,'mpdconf_advanced',0); 
			// 	}
			// }
			// break;
			
			// case 'check_source':
			// $hash = md5_file('/etc/auto.nas');
			// if ($hash != $_SESSION['sourceconfhash']) {
			// 	if ($_SESSION['sourceconf_advanced'] != 1) {
			// 	$this->playerSession('write',$db,'sourceconf_advanced',1); 
			// 	}
			// return false;
			// } else {
			// 	if ($_SESSION['sourceconf_advanced'] != 0) {
			// 	$this->playerSession('write',$db,'sourceconf_advanced',0); 
			// 	}
			// }
			// break;
			
			// case 'hash_net':
			// $hash = md5_file('/etc/network/interfaces');
			// $this->playerSession('write',$db,'netconfhash',$hash); 
			// break;
			
			// case 'hash_mpd':
			// $hash = md5_file('/etc/mpd.conf');
			// $this->playerSession('write',$db,'mpdconfhash',$hash); 
			// break;
			
			// case 'hash_source':
			// $hash = md5_file('/etc/auto.nas');
			// $this->playerSession('write',$db,'sourceconfhash',$hash); 
			// break;
		} 
		$this->playerSession('unlock');
		return true;
	}
	
	// debug functions
	function debug($input) 
	{
		session_start();
		// if $input = 1 clear SESSION debug data else load debug data into session
		if (isset($input) && $input == 1) 
		{
			$_SESSION['debugdata'] = '';
		} 
		else 
		{
			$_SESSION['debugdata'] = $input;
		}
		
		session_write_close();
	}
	
	function debug_footer($db) 
	{
		if ($_SESSION['debug'] > 0) 
		{
			debug_output();
			debug(1);
			echo "\n";
			echo "###### System info ######\n";
			echo  file_get_contents('/proc/version');
			echo "\n";
			echo  "system load:\t".file_get_contents('/proc/loadavg');
			echo "\n";
			echo "HW platform:\t".$_SESSION['hwplatform']." (".$_SESSION['hwplatformid'].")\n";
			echo "\n";
			echo "playerID:\t".$_SESSION['playerid']."\n";
			echo "\n";
			echo "\n";
			echo "###### Audio backend ######\n";
			echo  file_get_contents('/proc/asound/version');
			echo "\n";
			echo "Card list: (/proc/asound/cards)\n";
			echo "--------------------------------------------------\n";
			echo  file_get_contents('/proc/asound/cards');
			echo "\n";
			echo "ALSA interface #0: (/proc/asound/card0/pcm0p/info)\n";
			echo "--------------------------------------------------\n";
			echo  file_get_contents('/proc/asound/card0/pcm0p/info');
			echo "\n";
			echo "ALSA interface #1: (/proc/asound/card1/pcm0p/info)\n";
			echo "--------------------------------------------------\n";
			echo  file_get_contents('/proc/asound/card1/pcm0p/info');
			echo "\n";
			echo "interface #0 stream status: (/proc/asound/card0/stream0)\n";
			echo "--------------------------------------------------------\n";
			$streaminfo = file_get_contents('/proc/asound/card0/stream0');
			
			if (empty($streaminfo)) 
			{
				echo "no stream present\n";
			} 
			else 
			{
				echo $streaminfo;
			}
			
			echo "\n";
			echo "interface #1 stream status: (/proc/asound/card1/stream0)\n";
			echo "--------------------------------------------------------\n";
			$streaminfo = file_get_contents('/proc/asound/card1/stream0');
			if (empty($streaminfo)) 
			{
				echo "no stream present\n";
			} 
			else 
			{
				echo $streaminfo;
			}
			echo "\n";
			echo "\n";
			echo "###### Kernel optimization parameters ######\n";
			echo "\n";
			echo "hardware platform:\t".$_SESSION['hwplatform']."\n";
			echo "current orionprofile:\t".$_SESSION['orionprofile']."\n";
			echo "\n";
			// 		echo  "kernel scheduler for mmcblk0:\t\t".((empty(file_get_contents('/sys/block/mmcblk0/queue/scheduler'))) ? "\n" : file_get_contents('/sys/block/mmcblk0/queue/scheduler'));
			echo  "kernel scheduler for mmcblk0:\t\t".file_get_contents('/sys/block/mmcblk0/queue/scheduler');
			echo  "/proc/sys/vm/swappiness:\t\t".file_get_contents('/proc/sys/vm/swappiness');
			echo  "/proc/sys/kernel/sched_latency_ns:\t".file_get_contents('/proc/sys/kernel/sched_latency_ns');
			echo  "/proc/sys/kernel/sched_rt_period_us:\t".file_get_contents('/proc/sys/kernel/sched_rt_period_us');
			echo  "/proc/sys/kernel/sched_rt_runtime_us:\t".file_get_contents('/proc/sys/kernel/sched_rt_runtime_us');
			echo "\n";
			echo "\n";
			echo "###### Filesystem mounts ######\n";
			echo "\n";
			echo  file_get_contents('/proc/mounts');
			echo "\n";
			echo "\n";
			echo "###### mpd.conf ######\n";
			echo "\n";
			echo file_get_contents('/etc/mpd.conf');
			echo "\n";
			}
			if ($_SESSION['debug'] > 1) {
			echo "\n";
			echo "\n";
			echo "###### PHP backend ######\n";
			echo "\n";
			echo "php version:\t".phpVer()."\n";
			echo "debug level:\t".$_SESSION['debug']."\n";
			echo "\n";
			echo "\n";
			echo "###### SESSION ######\n";
			echo "\n";
			echo "STATUS:\t\t".session_status()."\n";
			echo "ID:\t\t".session_id()."\n"; 
			echo "SAVE PATH:\t".session_save_path()."\n";
			echo "\n";
			echo "\n";
			echo "###### SESSION DATA ######\n";
			echo "\n";
			print_r($_SESSION);
			}
			if ($_SESSION['debug'] > 2) {
			$connection = new pdo($db);
			$querystr="SELECT * FROM cfg_engine";
			$data['cfg_engine'] = $this->sdbquery($querystr,$connection);
			$querystr="SELECT * FROM cfg_lan";
			$data['cfg_lan'] = $this->sdbquery($querystr,$connection);
			$querystr="SELECT * FROM cfg_wifisec";
			$data['cfg_wifisec'] = $this->sdbquery($querystr,$connection);
			$querystr="SELECT * FROM cfg_mpd";
			$data['cfg_mpd'] = $this->sdbquery($querystr,$connection);
			$querystr="SELECT * FROM cfg_source";
			$data['cfg_source'] = $this->sdbquery($querystr,$connection);
			$connection = null;
			echo "\n";
			echo "\n";
			echo "###### SQLite datastore ######\n";
			echo "\n";
			echo "\n";
			echo "### table CFG_ENGINE ###\n";
			print_r($data['cfg_engine']);
			echo "\n";
			echo "\n";
			echo "### table CFG_LAN ###\n";
			print_r($data['cfg_lan']);
			echo "\n";
			echo "\n";
			echo "### table CFG_WIFISEC ###\n";
			print_r($data['cfg_wifisec']);
			echo "\n";
			echo "\n";
			echo "### table CFG_SOURCE ###\n";
			print_r($data['cfg_source']);
			echo "\n";
			echo "\n";
			echo "### table CFG_MPD ###\n";
			print_r($data['cfg_mpd']);
			echo "\n";
			}
			if ($_SESSION['debug'] > 0) {
			echo "\n";
			printf("Page created in %.5f seconds.", (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']));
			echo "\n";
			echo "\n";
			}
	}
	
	function debug_output($clear) 
	{
		if (!empty($_SESSION['debugdata'])) 
		{
			$output = print_r($_SESSION['debugdata']);
		}
		
		echo $output;
	}
	
	// search a string in a file and replace with another string the whole line.
	function wrk_replaceTextLine($file,$pos_start,$pos_stop,$strfind,$strrepl) {
		$fileData = file($file);
		$newArray = array();
		foreach($fileData as $line) {
		// find the line that starts with $strfind (search offset $pos_start / $pos_stop)
		if (substr($line, $pos_start, $pos_stop) == $strfind OR substr($line, $pos_start++, $pos_stop) == $strfind) {
			// replace presentation_url with current IP address
			$line = $strrepl."\n";
		}
		$newArray[] = $line;
		}
		return $newArray;
	}
	
	// make device TOTALBACKUP (with switch DEV copy all /etc)
	function wrk_backup($bktype) {
		if ($bktype == 'dev') {
		$filepath = "/run/totalbackup_".date('Y-m-d').".tar.gz";
		$cmdstring = "tar -czf ".$filepath." /var/lib/mpd /boot/cmdline.txt /var/www /etc";
		} else {
		$filepath = "/run/backup_".date('Y-m-d').".tar.gz";
		$cmdstring = "tar -czf ".$filepath." /var/lib/mpd /etc/auto.nas /etc/mpd.conf /var/www/db/player.db";
		}
		
	$this->sysCmd($cmdstring);
	return $filepath;
	}
	
	function wrk_restore($backupfile) {
	$path = "/run/".$backupfile;
	$cmdstring = "tar xzf ".$path." --overwrite --directory /";
		if ($this->sysCmd($cmdstring)) {
			recursiveDelete($path);
		}
	}
	
	function wrk_jobID() {
	$jobID = md5(uniqid(rand(), true));
	return "job_".$jobID;
	}
	
	function wrk_checkStrSysfile($sysfile,$searchstr) {
	$file = stripcslashes(file_get_contents($sysfile));
	// debug
	//error_log(">>>>> wrk_checkStrSysfile(".$sysfile.",".$searchstr.") >>>>> ",0);
		if (strpos($file, $searchstr)) {
		return true;
		} else {
		return false;
		}
	}
	
	function wrk_sourcemount($db,$action,$id) 
	{
		switch ($action) 
        {
			case 'mount':
				$dbh = $this->cfgdb_connect($db);
				$mp = $this->cfgdb_read('cfg_source',$dbh,'',$id);
				$this->sysCmd("mkdir \"/mnt/NAS/".$mp[0]['name']."\"");
				if ($mp[0]['type'] == 'cifs') 
                {
                    // smb/cifs mount
                    if (empty($mp[0]['username'])) 
                    {
                        $mp[0]['username'] = 'guest';
                    }
                    $mountstr = "mount -t cifs \"//".$mp[0]['address']."/".$mp[0]['remotedir']."\" -o username=".$mp[0]['username'].",password=".$mp[0]['password'].",rsize=".$mp[0]['rsize'].",wsize=".$mp[0]['wsize'].",iocharset=".$mp[0]['charset'].",".$mp[0]['options']." \"/mnt/NAS/".$mp[0]['name']."\"";
				} 
                else 
                {
				    // nfs mount
				    $mountstr = "mount -t nfs -o ".$mp[0]['options']." \"".$mp[0]['address'].":/".$mp[0]['remotedir']."\" \"/mnt/NAS/".$mp[0]['name']."\"";
				}
				// debug
				error_log(">>>>> mount string >>>>> ".$mountstr,0);
				$sysoutput = $this->sysCmd($mountstr);
				error_log(var_dump($sysoutput),0);
				if (empty($sysoutput)) 
                {
					if (!empty($mp[0]['error'])) 
                    {
                        $mp[0]['error'] = '';
                        $this->cfgdb_update('cfg_source',$dbh,'',$mp[0]);
					}
				    $return = 1;
				} 
                else
                {
                    $this->sysCmd("rmdir \"/mnt/NAS/".$mp[0]['name']."\"");
                    $mp[0]['error'] = implode("\n",$sysoutput);
                    $this->cfgdb_update('cfg_source',$dbh,'',$mp[0]);
                    $return = 0;
				}	
			break;
			
			case 'mountall':
                $dbh = $this->cfgdb_connect($db);
                $mounts = $this->cfgdb_read('cfg_source',$dbh);
                foreach ($mounts as $mp) 
                {
                    if (!$this->wrk_checkStrSysfile('/proc/mounts',$mp['name']) ) 
                    {
                        $return = $this->wrk_sourcemount($db,'mount',$mp['id']);
                    }
                }
                $dbh = null;
			break;
		}
        
	   return $return;
	}
	
	function wrk_sourcecfg($db,$queueargs) 
    {
        $action = $queueargs['mount']['action'];
        unset($queueargs['mount']['action']);
		switch ($action) 
        {
			case 'reset': 
                $dbh = $this->cfgdb_connect($db);
                $source = $this->cfgdb_read('cfg_source',$dbh);
                foreach ($source as $mp) 
                {
                    $this->sysCmd("umount -f \"/mnt/NAS/".$mp['name']."\"");
                    $this->sysCmd("rmdir \"/mnt/NAS/".$mp['name']."\"");
                }
                if ($this->cfgdb_delete('cfg_source',$dbh)) 
                {
                    $return = 1;
                } 
                else 
                {
                    $return = 0;
                }
                $dbh = null;
			break;
	
			case 'add':
                $dbh = $this->cfgdb_connect($db);
                print_r($queueargs);
                unset($queueargs['mount']['id']);
                // format values string
                foreach ($queueargs['mount'] as $key => $value) 
                {
                    if ($key == 'error') 
                    {
                        $values .= "'".SQLite3::escapeString($value)."'";
                        error_log(">>>>> values on line 1014 >>>>> ".$values, 0);
                    } 
                    else 
                    {
                        $values .= "'".SQLite3::escapeString($value)."',";
                        error_log(">>>>> values on line 1016 >>>>> ".$values, 0);
                    }
                }
                error_log(">>>>> values on line 1019 >>>>> ".$values, 0);
                // write new entry
                $this->cfgdb_write('cfg_source',$dbh,$values);
                $newmountID = $dbh->lastInsertId();
                $dbh = null;
                if ($this->wrk_sourcemount($db,'mount',$newmountID)) 
                {
                    $return = 1;
                } 
                else 
                {
                    $return = 0;
                }
			break;
			
			case 'edit':
                $dbh = $this->cfgdb_connect($db);
                $mp = $this->cfgdb_read('cfg_source',$dbh,'',$queueargs['mount']['id']);
                $this->cfgdb_update('cfg_source',$dbh,'',$queueargs['mount']);	
                $this->sysCmd("umount -f \"/mnt/NAS/".$mp[0]['name']."\"");
                if ($mp[0]['name'] != $queueargs['mount']['name']) 
                {
                    $this->sysCmd("rmdir \"/mnt/NAS/".$mp[0]['name']."\"");
                    $this->sysCmd("mkdir \"/mnt/NAS/".$queueargs['mount']['name']."\"");
                }
                if ($this->wrk_sourcemount($db,'mount',$queueargs['mount']['id'])) 
                {
                    $return = 1;
                } 
                else 
                {
                    $return = 0;
                }
                error_log(">>>>> wrk_sourcecfg(edit) exit status = >>>>> ".$return, 0);
                $dbh = null;
			break;
			case 'delete':
                $dbh = $this->cfgdb_connect($db);
                $mp = $this->cfgdb_read('cfg_source',$dbh,'',$queueargs['mount']['id']);
                $this->sysCmd("umount -f \"/mnt/NAS/".$mp[0]['name']."\"");
                $this->sysCmd("rmdir \"/mnt/NAS/".$mp[0]['name']."\"");
                if ($this->cfgdb_delete('cfg_source',$dbh,$queueargs['mount']['id'])) 
                {
                    $return = 1;
                } 
                else 
                {
                    $return = 0;
                }
                $dbh = null;
			break;
		}
	
	   return $return;
	}
	
	function wrk_getHwPlatform() 
    {
        $file = '/proc/cpuinfo';
		$fileData = file($file);
		foreach($fileData as $line) 
        {
			if (substr($line, 0, 8) == 'Hardware') 
            {
				$arch = trim(substr($line, 11, 50)); 
                switch($arch) 
                {
                    // RaspberryPi
                    case 'BCM2708':
                    $arch = '01';
                    break;
                    
                    // UDOO
                    case 'SECO i.Mx6 UDOO Board':
                    $arch = '02';
                    break;
                    
                    // CuBox
                    case 'Marvell Dove (Flattened Device Tree)':
                    $arch = '03';
                    break;
                    
                    // BeagleBone Black
                    case 'Generic AM33XX (Flattened Device Tree)':
                    $arch = '04';
                    break;
                    
                    // Compulab Utilite
                    case 'Compulab CM-FX6':
                    $arch = '05';
                    break;
                    
                    // Wandboard
                    case 'Freescale i.MX6 Quad/DualLite (Device Tree)':
                    $arch = '06';
                    break;
                    
                    // Cubieboard 
                    case 'sun7i':
                    $arch = '07';
                    break;
                    
                    // RaspberryPi 2
                    case 'BCM2709':
                    $arch = '08';
                    break;
                    
                    // Odroid C1
                    case 'ODROIDC':
                    $arch = '09';
                    break;
                    
                    default:
                    $arch = '--';
                    break;
                }
			}
		}
        
        if (!isset($arch)) 
        {
            $arch = '--';
        }
        
        return $arch;
	}
	
	function wrk_setHwPlatform($db) 
    {
        $arch = $this->wrk_getHwPlatform();
        $playerid = $this->wrk_playerID($arch);
        // register playerID into database
        $this->playerSession('write',$db,'playerid',$playerid);
        // register platform into database
		switch($arch) 
        {
			case '01':
                $this->playerSession('write',$db,'hwplatform','RaspberryPi');
                $this->playerSession('write',$db,'hwplatformid',$arch);
			break;
			
			case '02':
                $this->playerSession('write',$db,'hwplatform','UDOO');
                $this->playerSession('write',$db,'hwplatformid',$arch);
			break;
			
			case '03':
                $this->playerSession('write',$db,'hwplatform','CuBox');
                $this->playerSession('write',$db,'hwplatformid',$arch);
			break;
			
			case '04':
                $this->playerSession('write',$db,'hwplatform','BeagleBone Black');
                $this->playerSession('write',$db,'hwplatformid',$arch);
			break;
			
			case '05':
                $this->playerSession('write',$db,'hwplatform','Compulab Utilite');
                $this->playerSession('write',$db,'hwplatformid',$arch);
			break;
			
			case '06':
                $this->playerSession('write',$db,'hwplatform','Wandboard');
                $this->playerSession('write',$db,'hwplatformid',$arch);
			break;
			
			case '07':
                $this->playerSession('write',$db,'hwplatform','Cubieboard');
                $this->playerSession('write',$db,'hwplatformid',$arch);
			break;
			
			case '08':
                $this->playerSession('write',$db,'hwplatform','RaspberryPi2');
                $this->playerSession('write',$db,'hwplatformid',$arch);
			break;
			
			case '09':
                $this->playerSession('write',$db,'hwplatform','Odroid-C1');
                $this->playerSession('write',$db,'hwplatformid',$arch);
			break;
			
			default:
                $this->playerSession('write',$db,'hwplatform','unknown');
                $this->playerSession('write',$db,'hwplatformid',$arch);
            break;
		}
	}
	
	function wrk_playerID($arch) 
    {
        // $playerid = $arch.md5(uniqid(rand(), true)).md5(uniqid(rand(), true));
        $playerid = $arch.md5_file('/sys/class/net/eth0/address');
        return $playerid;
	}
	
	function wrk_sysChmod() 
    {
        $this->sysCmd('chmod -R 777 /var/www/db');
        $this->sysCmd('chmod a+x /var/www/command/orion_optimize.sh');
        $this->sysCmd('chmod 777 /run');
        $this->sysCmd('chmod 777 /run/sess*');
        $this->sysCmd('chmod a+rw /etc/mpd.conf');
	}
	
	function wrk_sysEnvCheck($arch,$install) 
    {
		if ($arch == '01' OR $arch == '02' OR $arch == '03' OR $arch == '04' OR $arch == '05' OR $arch == '06') {
		// /etc/rc.local
	//	 $a = '/etc/rc.local';
	//	 $b = '/var/www/_OS_SETTINGS/etc/rc.local';
	//	 if (md5_file($a) != md5_file($b)) {
	//	 $this->sysCmd('cp '.$b.' '.$a);
	//	 }
		
		// /etc/samba/smb.conf
	//	 $a = '/etc/samba/smb.conf';
	//	 $b = '/var/www/_OS_SETTINGS/etc/samba/smb.conf';
	//	 if (md5_file($a) != md5_file($b)) {
	//	 $this->sysCmd('cp '.$b.' '.$a.' ');
	//	 }
		// /etc/nginx.conf
		$a = '/etc/nginx/nginx.conf';
		$b = '/var/www/_OS_SETTINGS/etc/nginx/nginx.conf';
		if (md5_file($a) != md5_file($b)) {
		$this->sysCmd('cp '.$b.' '.$a.' ');
		// stop nginx
		$this->sysCmd('killall -9 nginx');
		// start nginx
		$this->sysCmd('nginx');
		}
		// /etc/php5/cli/php.ini
		$a = '/etc/php5/cli/php.ini';
		$b = '/var/www/_OS_SETTINGS/etc/php5/cli/php.ini';
		if (md5_file($a) != md5_file($b)) {
		$this->sysCmd('cp '.$b.' '.$a.' ');
		$restartphp = 1;
		}
		// /etc/php5/fpm/php-fpm.conf
		$a = '/etc/php5/fpm/php-fpm.conf';
		$b = '/var/www/_OS_SETTINGS/etc/php5/fpm/php-fpm.conf';
		if (md5_file($a) != md5_file($b)) {
		$this->sysCmd('cp '.$b.' '.$a.' ');
		$restartphp = 1;
		}
		// /etc/php5/fpm/php.ini
		$a = '/etc/php5/fpm/php.ini';
		$b = '/var/www/_OS_SETTINGS/etc/php5/fpm/php.ini';
		if (md5_file($a) != md5_file($b)) {
		$this->sysCmd('cp '.$b.' '.$a.' ');
		$restartphp = 1;
		}
		
			if ($install == 1) {
			// remove autoFS for NAS mount
			$this->sysCmd('cp /var/www/_OS_SETTINGS/etc/auto.master /etc/auto.master');
			$this->sysCmd('rm /etc/auto.nas');
			$this->sysCmd('service autofs restart');
			// /etc/php5/mods-available/apc.ini
			$this->sysCmd('cp /var/www/_OS_SETTINGS/etc/php5/mods-available/apc.ini /etc/php5/mods-available/apc.ini');
			// /etc/php5/fpm/pool.d/ erase
			$this->sysCmd('rm /etc/php5/fpm/pool.d/*');
			// /etc/php5/fpm/pool.d/ copy
			$this->sysCmd('cp /var/www/_OS_SETTINGS/etc/php5/fpm/pool.d/* /etc/php5/fpm/pool.d/');
			$restartphp = 1;
			}
			
		// /etc/php5/fpm/pool.d/command.conf
		$a = '/etc/php5/fpm/pool.d/command.conf';
		$b = '/var/www/_OS_SETTINGS/etc/php5/fpm/pool.d/command.conf';
		if (md5_file($a) != md5_file($b)) {
		$this->sysCmd('cp '.$b.' '.$a.' ');
		$restartphp = 1;
		}
		// /etc/php5/fpm/pool.d/db.conf
		$a = '/etc/php5/fpm/pool.d/db.conf';
		$b = '/var/www/_OS_SETTINGS/etc/php5/fpm/pool.d/db.conf';
		if (md5_file($a) != md5_file($b)) {
		$this->sysCmd('cp '.$b.' '.$a.' ');
		$restartphp = 1;
		}
		// /etc/php5/fpm/pool.d/display.conf
		$a = '/etc/php5/fpm/pool.d/display.conf';
		$b = '/var/www/_OS_SETTINGS/etc/php5/fpm/pool.d/display.conf';
		if (md5_file($a) != md5_file($b)) {
		$this->sysCmd('cp '.$b.' '.$a.' ');
		$restartphp = 1;
		}
			// (RaspberryPi arch)
	//		if ($arch == '01') {
	//		$a = '/boot/cmdline.txt';
	//			$b = '/var/www/_OS_SETTINGS/boot/cmdline.txt';
	//			if (md5_file($a) != md5_file($b)) {
	//			$this->sysCmd('cp '.$b.' '.$a.' ');
				// /etc/fstab
	//			$a = '/etc/fstab';
	//			$b = '/var/www/_OS_SETTINGS/etc/fstab_raspberry';
	//			if (md5_file($a) != md5_file($b)) {
	//				$this->sysCmd('cp '.$b.' '.$a.' ');
	//				$reboot = 1;
	//				}
	//			}
	//		}
			if (isset($restartphp) && $restartphp == 1) {
			$this->sysCmd('service php5-fpm restart');
			}
			if (isset($reboot) && $reboot == 1) {
			$this->sysCmd('reboot');
			}
		}	
	}
	
	
	function alsa_findHwMixerControl($device) 
    {
        if (isset($_SESSION['i2s']) && $_SESSION['i2s'] == 'Hifiberryplus') 
        {
            $hwmixerdev = 'Playback Digital';
        } 
        elseif (isset($_SESSION['i2s']) && $_SESSION['i2s'] == 'Hifiberry') 
        {
            $hwmixerdev = 'Playback Digital';
        } 
        elseif (isset($_SESSION['i2s']) && $_SESSION['i2s'] == 'Iqaudio') 
        {
            $hwmixerdev = 'Playback Digital';
        } 
        else 
        {
            $cmd = "amixer -c ".$device." |grep \"mixer control\"";
            $str = $this->sysCmd($cmd);
            $hwmixerdev = substr(substr($str[0], 0, -(strlen($str[0]) - strrpos($str[0], "'"))), strpos($str[0], "'")+1);
	   }
       
	   return $hwmixerdev;
	}
	
	function ui_notify($notify) 
    {
        $output .= "<script>";
        $output .= "jQuery(document).ready(function() {";
        $output .= "$.pnotify.defaults.history = false;";
        $output .= "$.pnotify({";
        $output .= "title: '".$notify['title']."',";
        $output .= "text: '".$notify['msg']."',";
        $output .= "icon: 'icon-ok',";
        $output .= "opacity: .9});";
        $output .= "});";
        $output .= "</script>";
        echo $output;
	}
	
	function ui_lastFM_coverart($artist,$album,$lastfm_apikey) 
    {
        $url = "http://ws.audioscrobbler.com/2.0/?method=album.getinfo&api_key=".$lastfm_apikey."&artist=".urlencode($artist)."&album=".urlencode($album)."&format=json";
        // debug
        //echo $url;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $output = json_decode($output,true);
        curl_close($ch);
        /* debug
        echo "<pre>";
        print_r($output);
        echo "</pre>";
        echo "<br>";
        */
        // key [3] == extralarge last.fm image
        return $output['album']['image'][3]['#text'];
	}
	
	// ACX Functions
	function sezione() 
    {
		echo '<pre><strong>sezione</strong> = '.$GLOBALS['sezione'].'</pre>';
	}
	
	function ami($sz = null) 
    {
		switch ($sz) 
        {
			case 'index':
				echo (in_array($GLOBALS['sezione'], array(
					'index'
					))?'active':'');
				break;
			case 'sources':
				echo (in_array($GLOBALS['sezione'], array(
					'sources', 'sources-add', 'sources-edit'
					))?'active':'');
				break;
			case 'mpd-config':
				echo (in_array($GLOBALS['sezione'], array(
					'mpd-config'
					))?'active':'');
				break;
			case 'mpd-config-network':
				echo (in_array($GLOBALS['sezione'], array(
					'mpd-config-network'
					))?'active':'');
				break;
			case 'system':
				echo (in_array($GLOBALS['sezione'], array(
					'system'
					))?'active':'');
				break;
			case 'help':
				echo (in_array($GLOBALS['sezione'], array(
					'help'
					))?'active':'');
				break;
			case 'credits':
				echo (in_array($GLOBALS['sezione'], array(
					'credits'
					))?'active':'');
				break;
		}	
	}
	
	function current_item($sez=null) 
    {
		echo (($GLOBALS['sezione'] == $sez)?' class="current"':'');
	}
}
