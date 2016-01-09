<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Volumio\Services\ConnectionService;
use App\Volumio\Mpd\MpdService;

class SettingsController extends Controller
{
    protected $connectionService;
    protected $mpdService;

    public function __construct(ConnectionService $connectionService, MpdService $mpdService)
    {
        $this->connectionService = $connectionService;
        $this->mpdService = $mpdService;
    }
    
    public function index(Request $request)
    {
        $w_lock = $request->session()->get("w_lock");
        $w_queue = $request->session()->get("w_queue");
        $w_active = $request->session()->get("w_active");
        $notify = $request->session()->get("notify");
        $sessionSpotify = $request->session()->get("spotify");
        $sessionSpotifybitrate = $request->session()->get("spotifybitrate");
        $sessionSpotpassword = $request->session()->get("spotpassword");
        $sessionSpotusername = $request->session()->get("spotusername");
        $sessionI2s = $request->session()->get("i2s");
        $sessionDisplaylibastab = $request->session()->get("displaylibastab");
        $sessionDisplaylib = $request->session()->get("displaylib");
        $sessionHostname = $request->session()->get("hostname");
        $sessionStartupSound = $request->session()->get("startupsound");
        $sessionMinidlna = $request->session()->get("minidlna");
        $sessionDjmount = $request->session()->get("djmount");
        $sessionUpnpmpdcli = $request->session()->get("upnpmpdcli");
        $sessionShairport = $request->session()->get("shairport");
        $sessionCmediafix = $request->session()->get("cmediafix");
        $sessionOrionProfile = $request->session()->get("orionprofile");
        
        $arch = $this->connectionService->wrk_getHwPlatform();
        $divi2s = "";
        if ($arch != '01' && $arch != '08')
        {
            $divi2s = "class=\"hide\"";
        }

        return view("settings", [
            "w_lock" => $w_lock,
            "w_queue" => $w_queue,
            "w_active" => $w_active,
            "spotify" => $sessionSpotify,
            "spotifybitrate" => $sessionSpotifybitrate,
            "spotpassword" => $sessionSpotpassword,
            "spotusername" => $sessionSpotusername,
            "i2s" => $sessionI2s,
            "displaylibastab" => $sessionDisplaylibastab,
            "displaylib" => $sessionDisplaylib,
            "hostname" => $sessionHostname,
            "startupsound" => $sessionStartupSound,
            "minidlna" => $sessionMinidlna,
            "djmount" => $sessionDjmount,
            "upnpmpdcli" => $sessionUpnpmpdcli,
            "shairport" => $sessionShairport,
            "cmediafix" => $sessionCmediafix,
            "orionprofile" => $sessionOrionProfile,
            "divi2s" => $divi2s,
        ]);
    }

    public function postIndex(Request $request)
    {
        $syscmd = $request->input('syscmd');
        $w_lock = $request->session()->get("w_lock");
        $w_queue = $request->session()->get("w_queue");
        $w_active = $request->session()->get("w_active");
        $notify = $request->session()->get("notify");
        $db = 'sqlite:'.$_SERVER['DOCUMENT_ROOT'].'/db/player.db';
        
        if (isset($syscmd))
        {
            switch ($syscmd)
            {
                case 'reboot':
                    if ($w_lock != 1 && $w_queue == '')
                    {
                        // start / respawn session

                        session_start();
                        $request->session()->put("w_queue", "reboot");
                        $request->session()->put("w_active", 1);

                        // set UI notify

                        $notify['title'] = 'REBOOT';
                        $notify['msg'] = 'reboot player initiated...';
                        $request->session()->put("notify", $notify);

                        // unlock session file

                        $this->connectionService->playerSession('unlock');
                    }
                    else
                    {
                        echo "background worker busy";
                    }

                    // unlock session file

                    $this->connectionService->playerSession('unlock');
                    break;

                case 'poweroff':
                    if ($w_lock != 1 && $w_queue == '')
                    {

                        // start / respawn session

                        session_start();
                        $request->session()->put("w_queue", "poweroff");
                        $request->session()->put("w_active", 1);

                        // set UI notify

                        $notify['title'] = 'SHUTDOWN';
                        $notify['msg'] = 'shutdown player initiated...';
                        $request->session()->put("notify", $notify);

                        // unlock session file

                        $this->connectionService->playerSession('unlock');
                    }
                    else
                    {
                        echo "background worker busy";
                    }

                    break;

                case 'mpdrestart':
                    if ($w_lock != 1 && $w_queue == '')
                    {

                        // start / respawn session

                        session_start();
                        $request->session()->put("w_queue", "mpdrestart");
                        $request->session()->put("w_active", 1);

                        // set UI notify

                        $notify['title'] = 'MPD RESTART';
                        $notify['msg'] = 'restarting MPD daemon...';
                        $request->session()->put("notify", $notify);

                        // unlock session file

                        $this->connectionService->playerSession('unlock');
                    }
                    else
                    {
                        echo "background worker busy";
                    }

                    break;

                case 'backup':
                    if ($w_lock != 1 && $w_queue == '')
                    {

                        // start / respawn session

                        session_start();
                        $request->session()->put("w_queue", "backup");
                        $request->session()->put("w_active", 1);
                        $request->session()->put("w_jobID", wrk_jobID());
                        $this->connectionService->playerSession('unlock');

                        // wait worker response loop

                        while (1)
                        {
                            sleep(2);
                            session_start();
                            $w_JobId = $request->session()->get($request->session()->get("w_jobID"));
                            if (isset($w_JobId))
                            {
                                // set UI notify

                                $notify['title'] = 'BACKUP';
                                $notify['msg'] = 'backup complete.';
                                $request->session()->put("notify", $notify);
                                
                                $this->connectionService->pushFile($w_JobId);
                                $request->session()->put($request->session()->get("w_jobID"), "");
                                //unset($_SESSION[$_SESSION['w_jobID']]);
                                break;
                            }

                            session_write_close();
                        }
                    }
                    else
                    {
                        session_start();
                        $notify['title'] = 'Job Failed';
                        $notify['msg'] = 'background worker is busy.';
                        $request->session()->put("notify", $notify);
                    }

                    // unlock session file

                    $this->connectionService->playerSession('unlock');
                    break;

                case 'updatempdDB':
                    if ($w_lock != 1 && $w_queue == '')
                    {
                        session_start();
                        $this->mpdService->sendMpdCommand($mpd, 'update');

                        // set UI notify

                        $notify['title'] = 'MPD Update';
                        $notify['msg'] = 'database update started...';
                        $request->session()->put("notify", $notify);

                        // unlock session file

                        $this->connectionService->playerSession('unlock');
                    }
                    else
                    {
                        echo "background worker busy";
                        $this->connectionService->playerSession('unlock');
                    }

                    break;

                case 'clearqueue':
                    if ($w_lock != 1 && $w_queue == '')
                    {
                        session_start();
                        $this->mpdService->sendMpdCommand($mpd, 'clear');

                        // set UI notify

                        $notify['title'] = 'Clear Queue';
                        $notify['msg'] = 'Play Queue Cleared';
                        $request->session()->put("notify", $notify);

                        // unlock session file

                        $this->connectionService->playerSession('unlock');
                    }
                    else
                    {
                        echo "background worker busy";
                    }

                    // unlock session file

                    $this->connectionService->playerSession('unlock');
                    break;

                case 'updateui':
                    if ($w_lock != 1 && $w_queue == '')
                    {

                        // start / respawn session

                        session_start();
                        $request->session()->put("w_queue", "updateui");
                        $request->session()->put("w_active", 1);

                        // set UI notify

                        $notify['title'] = 'Update';
                        $notify['msg'] = 'Retrieving Updates, if available';
                        $request->session()->put("notify", $notify);

                        // unlock session file

                        $this->connectionService->playerSession('unlock');
                    }
                    else
                    {
                        echo "background worker busy";
                    }

                    break;

                case 'totalbackup':
                    break;

                case 'restore':
                    break;
            }
        }

        // Show i2s selector only on RaspberryPI

        $arch = $this->connectionService->wrk_getHwPlatform();
        $divi2s = "";
        if ($arch != '01' && $arch != '08')
        {
            $divi2s = "class=\"hide\"";
        }
        
        $orionProfile = $request->input('orionprofile');
        $sessionOrionProfile = $request->session()->get("orionprofile");

        if (isset($orionProfile) && $orionProfile != $sessionOrionProfile)
        {
            // load worker queue

            if ($w_lock != 1 && $w_queue == '')
            {
                // start / respawn session

                session_start();
                $request->session()->put("w_queue", "orionprofile");
                $request->session()->put("w_queueargs", $orionProfile);

                // set UI notify

                $notify['title'] = 'KERNEL PROFILE';
                $notify['msg'] = 'orionprofile changed <br /> current profile:     <strong>' . $orionProfile . "</strong>";
                $request->session()->put("notify", $notify);

                // unlock session file

                $this->connectionService->playerSession('unlock');
            }
            else
            {
                echo "background worker busy";
            }

            // activate worker job

            if ($w_lock != 1)
            {
                // start / respawn session

                session_start();
                $request->session()->put("w_active", 1);

                // save new value on SQLite datastore

                $this->connectionService->playerSession('write', $db, 'orionprofile', $orionProfile);

                // unlock session file

                $this->connectionService->playerSession('unlock');
            }
            else
            {
                return "background worker busy";
            }
        }
        
        $cmediafix = $request->input('cmediafix');
        $sessionCmediafix = $request->session()->get("cmediafix");

        if (isset($cmediafix) && $cmediafix != $sessionCmediafix)
        {
            // load worker queue
            // start / respawn session

            session_start();

            // save new value on SQLite datastore

            if ($cmediafix == 1 || $cmediafix == 0)
            {
                $this->connectionService->playerSession('write', $db, 'cmediafix', $cmediafix);
            }

            // set UI notify
            $notify['title'] = '';
            $notify['msg'] = 'CMediaFix ' . $cmediafix == 1 ? "enabled" : "disabled";
            $request->session()->put("notify", $notify);

            // unlock session file

            $this->connectionService->playerSession('unlock');
        }
        
        $shairport = $request->input('shairport');
        $sessionShairport = $request->session()->get("shairport");

        if (isset($shairport) && $shairport != $sessionShairport)
        {
            // load worker queue
            // start / respawn session

            session_start();

            // save new value on SQLite datastore

            if ($shairport == 1 OR $shairport == 0)
            {
                $this->connectionService->playerSession('write', $db, 'shairport', $shairport);
            }

            // set UI notify
            $notify['title'] = "Airplay capability " . $shairport == 1 ? "enabled" : "disabled";
            $notify['msg'] = 'You must reboot for changes to take effect';
            $request->session()->put("notify", $notify);

            // unlock session file

            $this->connectionService->playerSession('unlock');
        }
        
        $upnpmpdcli = $request->input('upnpmpdcli');
        $sessionUpnpmpdcli = $request->session()->get("upnpmpdcli");
        
        if (isset($upnpmpdcli) && $upnpmpdcli != $sessionUpnpmpdcli)
        {
            // load worker queue
            // start / respawn session

            session_start();

            // save new value on SQLite datastore

            if ($upnpmpdcli == 1 OR $upnpmpdcli == 0)
            {
                $this->connectionService->playerSession('write', $db, 'upnpmpdcli', $upnpmpdcli);
            }

            // set UI notify
            $notify['title'] = "UPNP Control " . $upnpmpdcli == 1 ? "enabled" : "disabled";
            $notify['msg'] = 'You must reboot for changes to take effect';
            $request->session()->put("notify", $notify);

            // unlock session file

            $this->connectionService->playerSession('unlock');
        }
        
        $djmount = $request->input('djmount');
        $sessionDjmount = $request->session()->get("djmount");

        if (isset($djmount) && $djmount != $sessionDjmount)
        {
            // load worker queue
            // start / respawn session

            session_start();

            // save new value on SQLite datastore

            if ($djmount == 1 OR $djmount == 0)
            {
                $this->connectionService->playerSession('write', $db, 'djmount', $djmount);
            }

            // set UI notify
            $notify['title'] = "UPNP\DLNA Indexing" . $djmount == 1 ? "enabled" : "disabled";
            $notify['msg'] = 'You must reboot for changes to take effect';
            $request->session()->put("notify", $notify);

            // unlock session file

            $this->connectionService->playerSession('unlock');
        }

        $minidlna = $request->input('minidlna');
        $sessionMinidlna = $request->session()->get("minidlna");

        if (isset($minidlna) && $minidlna != $sessionMinidlna)
        {
            // load worker queue
            // start / respawn session

            session_start();

            // save new value on SQLite datastore

            if ($minidlna == 1 OR $minidlna == 0)
            {
                $this->connectionService->playerSession('write', $db, 'minidlna', $minidlna);
            }

            // set UI notify
            $notify['title'] = "DLNA Library Server" . $minidlna == 1 ? "enabled" : "disabled";
            $notify['msg'] = 'You must reboot for changes to take effect';
            $request->session()->put("notify", $notify);

            // unlock session file

            $this->connectionService->playerSession('unlock');
        }
        
        $startupSound = $request->input('startupsound');
        $sessionStartupSound = $request->session()->get("startupsound");

        if (isset($startupSound) && $startupSound != $sessionStartupSound)
        {
            // load worker queue
            // start / respawn session

            session_start();

            // save new value on SQLite datastore

            if ($startupSound == 1 OR $startupSound == 0)
            {
                $this->connectionService->playerSession('write', $db, 'startupsound', $startupSound);
            }

            // set UI notify
            $notify['title'] = '';
            $notify['msg'] = "Startup Sound " . $startupSound == 1 ? "enabled" : "disabled";
            $request->session()->put("notify", $notify);

            // unlock session file

            $this->connectionService->playerSession('unlock');
        }
        
        $hostname = $request->input('hostname');
        $sessionHostname = $request->session()->get("hostname");

        if (isset($hostname) && $hostname != $sessionHostname)
        {

            // load worker queue
            // start / respawn session

            session_start();

            // save new value on SQLite datastore

            $this->connectionService->playerSession('write', $db, 'hostname', $hostname);

            // replacing hostname with selected one. Dirty fix, avoids to set dangerous permissions to www-data

            $hfile = '/etc/hostname';
            $hn = "" . $sessionHostname;
            file_put_contents($hfile, $hn);
            $hsfile = '/etc/hosts';
            $hs = "127.0.0.1       localhost        " . $sessionHostname;
            file_put_contents($hsfile, $hs);
            
            $request->session()->put("w_queue", "hostname");            
            $request->session()->put("w_queueargs", $hostname);

            // set UI notify

            $notify['title'] = 'Player Name Changed';
            $notify['msg'] = 'You must reboot for changes to take effect';
            $request->session()->put("notify", $notify);

            // active worker queue
            $request->session()->put("w_active", 1);
        }
        else
        {
            $notify['title'] = 'Player Name Changed';
            $notify['msg'] = 'You must reboot for changes to take effect';
            $request->session()->put("notify", $notify);

            // open to read and modify
            // unlock session file

            $this->connectionService->playerSession('unlock');
        }

        $displaylib = $request->input('displaylib');
        $sessionDisplaylib = $request->session()->get("displaylib");
        // Library Display

        if (isset($displaylib) && $displaylib != $sessionDisplaylib)
        {
            // load worker queue
            // start / respawn session

            session_start();

            // save new value on SQLite datastore

            if ($displaylib == 1 OR $displaylib == 0)
            {
                $this->connectionService->playerSession('write', $db, 'displaylib', $displaylib);
            }

            // set UI notify

            $notify['title'] = '';
            $notify['msg'] = "Library view " . $displaylib == 1 ? "enabled" : "disabled";
            $request->session()->put("notify", $notify);

            // unlock session file

            $this->connectionService->playerSession('unlock');
        }

        $displaylibastab = $request->input('displaylibastab');
        $sessionDisplaylibastab = $request->session()->get("displaylibastab");
        
        if (isset($displaylibastab) && $displaylibastab != $sessionDisplaylibastab)
        {
            // load worker queue
            // start / respawn session

            session_start();

            // save new value on SQLite datastore

            if ($displaylibastab == 1 OR $displaylibastab == 0)
            {
                $this->connectionService->playerSession('write', $db, 'displaylibastab', $displaylibastab);
            }

            // unlock session file

            $this->connectionService->playerSession('unlock');
        }

        $i2s = $request->input('i2s');
        $sessionI2s = $request->session()->get("i2s");
        // i2s selector

        if (isset($i2s) && $i2s != $sessionI2s)
        {
            switch ($i2s)
            {
            case 'Hifiberry':
                session_start();
                $file = '/boot/config.txt';
                $text = 'gpu_mem=16
        hdmi_drive=2
        dtoverlay=hifiberry-dac';
                file_put_contents($file, $text);
                $notify['msg'] = 'Hifiberry Driver Activated. You must reboot for changes to take effect';
                $request->session()->put("notify", $notify);
                $request->session()->put("w_active", 1);

                // save new value on SQLite datastore

                $this->connectionService->playerSession('write', $db, 'i2s', $i2s);

                // unlock session file

                $this->connectionService->playerSession('unlock');
                break;

            case 'Hifiberryplus':
                session_start();
                $file = '/boot/config.txt';
                $text = 'gpu_mem=16
        hdmi_drive=2
        dtoverlay=hifiberry-dacplus';
                file_put_contents($file, $text);
                $notify['msg'] = 'Hifiberry + Driver Activated. You must reboot for changes to take effect';
                $request->session()->put("notify", $notify);
                // save new value on SQLite datastore

                $this->connectionService->playerSession('write', $db, 'i2s', $i2s);

                // unlock session file

                $this->connectionService->playerSession('unlock');
                break;

            case 'HifiberryDigi':
                session_start();
                $file = '/boot/config.txt';
                $text = 'gpu_mem=16
        hdmi_drive=2
        dtoverlay=hifiberry-digi';
                file_put_contents($file, $text);
                $notify['msg'] = 'Hifiberry DIGI Driver Activated. You must reboot for changes to take effect';
                $request->session()->put("notify", $notify);

                // save new value on SQLite datastore

                $this->connectionService->playerSession('write', $db, 'i2s', $i2s);

                // unlock session file

                $this->connectionService->playerSession('unlock');
                break;

            case 'HifiberryAmp':
                session_start();
                $file = '/boot/config.txt';
                $text = 'gpu_mem=16
        hdmi_drive=2
        dtoverlay=hifiberry-amp';
                file_put_contents($file, $text);
                $notify['msg'] = 'Hifiberry Amp Driver Activated. You must reboot for changes to take effect';
                $request->session()->put("notify", $notify);

                // save new value on SQLite datastore

                $this->connectionService->playerSession('write', $db, 'i2s', $i2s);

                // unlock session file

                $this->connectionService->playerSession('unlock');
                break;

            case 'Iqaudio':
                session_start();
                $file = '/boot/config.txt';
                $text = 'gpu_mem=16
        hdmi_drive=2
        dtoverlay=iqaudio-dac';
                file_put_contents($file, $text);
                $notify['msg'] = 'IQaudIO Pi-DAC Driver Activated. You must reboot for changes to take effect';
                $request->session()->put("notify", $notify);

                // save new value on SQLite datastore

                $this->connectionService->playerSession('write', $db, 'i2s', $i2s);

                // unlock session file

                $this->connectionService->playerSession('unlock');
                break;

            case 'IqaudioDacPlus':
                session_start();
                $file = '/boot/config.txt';
                $text = 'gpu_mem=16
        hdmi_drive=2
        dtoverlay=iqaudio-dacplus';
                file_put_contents($file, $text);
                $notify['msg'] = 'IQaudIO Pi-DAC Driver Activated. You must reboot for changes to take effect';
                $request->session()->put("notify", $notify);

                // save new value on SQLite datastore

                $this->connectionService->playerSession('write', $db, 'i2s', $i2s);

                // unlock session file

                $this->connectionService->playerSession('unlock');
                break;

            case 'RpiDac':
                session_start();
                $file = '/boot/config.txt';
                $text = 'gpu_mem=16
        hdmi_drive=2
        dtoverlay=rpi-dac';
                file_put_contents($file, $text);
                $notify['msg'] = 'RPi-DAC Driver Activated. You must reboot for changes to take effect';
                $request->session()->put("notify", $notify);

                // save new value on SQLite datastore

                $this->connectionService->playerSession('write', $db, 'i2s', $i2s);

                // unlock session file

                $this->connectionService->playerSession('unlock');
                break;

            case 'Generic':
                session_start();
                $file = '/boot/config.txt';
                $text = 'gpu_mem=16
        hdmi_drive=2
        dtoverlay=rpi-dac';
                file_put_contents($file, $text);
                $notify['msg'] = 'Generic Driver Activated. You must reboot for changes to take effect';
                $request->session()->put("notify", $notify);

                // save new value on SQLite datastore

                $this->connectionService->playerSession('write', $db, 'i2s', $i2s);

                // unlock session file

                $this->connectionService->playerSession('unlock');
                break;

            case 'i2soff':
                session_start();
                $file = '/boot/config.txt';
                $text = 'gpu_mem=16
        hdmi_drive=2h';
                $notify['msg'] = 'I2S Driver Deactivated. You must reboot for changes to take effect';
                $request->session()->put("notify", $notify);

                // save new value on SQLite datastore

                $this->connectionService->playerSession('write', $db, 'i2s', $i2s);

                // unlock session file

                $this->connectionService->playerSession('unlock');
                break;
            }
        }

        $spotusername = $request->input('spotusername');
        $sessionSpotusername = $request->session()->get("spotusername");
        // Spotify configuration File for Spop Daemon

        if (isset($spotusername) && $spotusername != $sessionSpotusername)
        {
            session_start();
            $this->connectionService->playerSession('write', $db, 'spotusername', $spotusername);
            $request->session()->put("w_queue", "spotusername");
            $request->session()->put("w_queueargs", $spotusername);
            $request->session()->put("w_active", 1);
        }
        else
        {
            $notify['title'] = 'Job Failed';
            $notify['msg'] = 'background worker is busy.';
            $request->session()->put("notify", $notify);
            
            $this->connectionService->playerSession('unlock');
        }

        $spotpassword = $request->input('spotpassword');
        $sessionSpotpassword = $request->session()->get("spotpassword");
        
        if (isset($spotpassword) && $spotpassword != $sessionSpotpassword)
        {
            session_start();
            $this->connectionService->playerSession('write', $db, 'spotpassword', $spotpassword);
            $request->session()->put("w_queue", "spotpassword");
            $request->session()->put("w_queueargs", $spotpassword);
            $request->session()->put("w_active", 1);
        }
        else
        {
            $notify['title'] = 'Job Failed';
            $notify['msg'] = 'background worker is busy.';
            $request->session()->put("notify", $notify);            
            $this->connectionService->playerSession('unlock');
        }

        $spotifybitrate = $request->input('spotifybitrate');
        $sessionSpotifybitrate = $request->session()->get("spotifybitrate");
        
        if (isset($spotifybitrate) && $spotifybitrate != $sessionSpotifybitrate)
        {
            session_start();
            if ($spotifybitrate == 1 OR $spotifybitrate == 0)
            {
                $this->connectionService->playerSession('write', $db, 'spotifybitrate', $spotifybitrate);
            }

            $this->connectionService->playerSession('unlock');
        }
        
        $spotify = $request->input('spotify');
        $sessionSpotify = $request->session()->get("spotify");

        if (isset($spotify) && $spotify != $sessionSpotify)
        {
            session_start();
            if ($spotify == 1 OR $spotify == 0)
            {
                $this->connectionService->playerSession('write', $db, 'spotify', $spotify);
            }

            $dbh = $this->connectionService->cfgdb_connect($db);
            $query_cfg = "SELECT param,value_player FROM cfg_mpd WHERE value_player!=''";
            $mpdcfg = $this->connectionService->sdbquery($query_cfg, $dbh);
            $dbh = null;
            foreach($mpdcfg as $cfg)
            {
                if ($cfg['param'] == 'audio_output_format' && $cfg['value_player'] == 'disabled')
                {
                    $output.= '';
                }
                else
                if ($cfg['param'] == 'device')
                {
                    $device = $cfg['value_player'];
                    var_export($device);
                }
                else
                {
                    $output.= $cfg['param'] . " \t\"" . $cfg['value_player'] . "\"\n";
                }
            }

            $spopconf = '/etc/spopd.conf';

            // $content .= "\t\t device \t\"hw:".$spotusername.",0\"\n";

            $content.= "[spop]" . "\n";
            $content.= "spotify_username = " . $request->session()->get("spotusername") . "\n";
            $content.= "spotify_password = " . $request->session()->get('spotpassword') . "\n";
            $content.= "audio_output = sox" . "\n";
            $content.= "[sox]" . "\n";
            $content.= "output_type = alsa" . "\n";
            $content.= "output_name =  plughw:" . $device . "" . "\n";
            if ($spotifybitrate == 0)
            {
                $content.= "high_bitrate = false" . "\n";
            }

            file_put_contents($spopconf, $content);
            $cmd = 'spopd -c /etc/spopd.conf > /dev/null 2>&1 &';

            // set UI $notify
            
            $request->session()->put("w_queue", "spotify");            
            $request->session()->put("w_queueargs", $spotify);
            
            $notify['title'] = "Spotify Service " . $spotify == 1 ? "enabled" : "disabled";
            $notify['msg'] = 'You must reboot for changes to take effect';
            $request->session()->put("notify", $notify);

            // unlock session file

            $this->connectionService->playerSession('unlock');
        }
        
        return view("settings", [
            "w_lock" => $w_lock,
            "w_queue" => $w_queue,
            "w_active" => $w_active,
            "spotify" => $sessionSpotify,
            "spotifybitrate" => $sessionSpotifybitrate,
            "spotpassword" => $sessionSpotpassword,
            "spotusername" => $sessionSpotusername,
            "i2s" => $sessionI2s,
            "displaylibastab" => $sessionDisplaylibastab,
            "displaylib" => $sessionDisplaylib,
            "hostname" => $sessionHostname,
            "startupsound" => $sessionStartupSound,
            "minidlna" => $sessionMinidlna,
            "djmount" => $sessionDjmount,
            "upnpmpdcli" => $sessionUpnpmpdcli,
            "shairport" => $sessionShairport,
            "cmediafix" => $sessionCmediafix,
            "orionprofile" => $sessionOrionProfile,
            "divi2s" => $divi2s
        ]);
    }
}
 
