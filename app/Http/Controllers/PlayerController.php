<?php

namespace App\Http\Controllers;

use App\Http\Services\AlbumArtService;
use App\Http\Services\ConnectionService;
use App\Http\Services\SpotifyService;
use App\Http\Services\MpdService;
use App\Http\Services\PandoraService;
use App\User;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    protected $connectionService;
    protected $albumArtService;
    protected $spotifyService;
    protected $mpdService;

    public function __construct(ConnectionService $connectionService, SpotifyService $spotifyService, MpdService $mpdService, AlbumArtService $albumArtService)
    {
        $this->connectionService = $connectionService;
        $this->albumArtService = $albumArtService;
        $this->spotifyService = $spotifyService;
        $this->mpdService = $mpdService;
    }
    
    function sendCommand(Request $request)
    {
        $commandName = $request->input('cmd');
        
        if ($commandName) 
        {            
            $path = $request->input('path');
            $p2 = $request->input('p2');
            
            switch ($commandName) 
            {
                case 'filepath':
                    if ($path) 
                    {
                        if (strcmp(substr($path,0,7),"SPOTIFY") == 0) 
                        {
                            $arraySpopSearchResults = $this->spotifyService->querySpopDB('filepath', $path);
                            echo json_encode($arraySpopSearchResults);
                        } 
                        else 
                        {
                            $arrayMpdSearchResults = $this->mpdService->searchDB('filepath', $path);
                            echo json_encode($arrayMpdSearchResults);
                        }	
                    } 
                    else 
                    {
                        $mpdSearchResults = $this->mpdService->searchDB('filepath');
                        $spopSearchResults = $this->spotifyService->querySpopDB('filepath');
                        //dd($spopSearchResults);
                        $pandoraSearchResults = [
                            [
                                "directory" => "Pandora",
                                "Name" => "PANDORA",
                                "serviceType" => "Pandora",
                                "Type" => "PandoraDirectory"
                            ]
                        ];
                        //dd($pandoraSearchResults);
                        $searchResults = array_merge($mpdSearchResults, $spopSearchResults, $pandoraSearchResults);
                    
                        //$response = $this->pandoraService->getPlaylists();
        
                        echo json_encode($searchResults);
                    }
        
                    break;
        
                case 'playlist':
                    echo json_encode($this->mpdService->getPlayQueue());
                    break;
        
                case 'add':
                    if (isset($path) && $path != '') 
                    {
                        echo json_encode($this->mpdService->addQueue($path));
                    }
                    break;
                
                case 'addplay':
                    if (isset($path) && $path != '') 
                    {
                        $status = $this->connectionService->_parseStatusResponse($this->mpdService->MpdStatus());
                        $pos = $status['playlistlength'] ;
                        $this->mpdService->addQueue($path);
                        $this->mpdService->sendMpdCommand('play ' . $pos);
                        echo json_encode($this->mpdService->readMpdResponse());
                    }
                    break;
        
                case 'addreplaceplay':
                    if (isset($path) && $path != '') 
                    {
                        $this->mpdService->sendMpdCommand('clear');
                        $this->mpdService->addQueue($path);
                        $this->mpdService->sendMpdCommand('play');
                        echo json_encode($this->mpdService->readMpdResponse());
                    }
                    break;
                
                case 'update':
                    if (isset($path) && $path != '') 
                    {
                        $this->mpdService->sendMpdCommand("update \"".html_entity_decode($path)."\"");
                        echo json_encode($this->mpdService->readMpdResponse());
                    }
                    break;
                
                case 'trackremove':
                    if (isset($_GET['songid']) && $_GET['songid'] != '') 
                    {
                        echo json_encode($this->mpdService->remTrackQueue($_GET['songid']));
                    }
                    break;
        
                case 'savepl':
                    if (isset($_GET['plname']) && $_GET['plname'] != '')
                    {
                        $this->mpdService->sendMpdCommand("rm \"".html_entity_decode($_GET['plname'])."\"");
                        $this->mpdService->sendMpdCommand("save \"".html_entity_decode($_GET['plname'])."\"");
                        echo json_encode($this->mpdService->readMpdResponse());
                    }
                    break;
                
                case 'search':
                    if (isset($_POST['query']) && $_POST['query'] != '' && isset($_GET['querytype']) && $_GET['querytype'] != '') 
                    {
                        $arraySearchResults = $this->mpdService->searchDB($_GET['querytype'],$_POST['query']);
        
                        $arraySpopSearchResults = $this->spotifyService->querySpopDB('file', $_POST['query']);
                        $arraySearchResults = array_merge($arraySearchResults, $arraySpopSearchResults);

                        echo json_encode($arraySearchResults);
                    }
        
                    break;
        
                case 'loadlib':
                    echo $this->mpdService->loadAllLib();
                    break;
        
                case 'playall':
                    if (isset($path) && $path != '') 
                    {
                        echo json_encode($this->mpdService->playAll($path));
                    }
                    break;
        
                case 'addall':
                    if (isset($path) && $path != '') 
                    {
                        echo json_encode($this->mpdService->enqueueAll($path));
                    }
                    break;	
                case 'spop-playplaylistindex':
                    if (isset($path) && $path != '') 
                    {
                        $sSpopPlaylistIndex = end(explode("@", $path));
                        $this->mpdService->sendMpdCommand('stop');
                        echo $this->spotifyService->sendCommand("play " . $sSpopPlaylistIndex);
                    }
                    break;
        
                case 'spop-addplaylistindex':
                    if (isset($path) && $path != '') 
                    {
                        $sSpopPlaylistIndex = end(explode("@", $path));
                        echo $this->spotifyService->sendCommand("add " . $sSpopPlaylistIndex);
                    }
                    break;
                default:
                    $spopCommandPos = strpos($commandName, "spop-");
                    
                    if($spopCommandPos != -1) 
                    {
                        $spopCommand = trim(substr($commandName, 5, strlen($commandName) - 5));
                    
                        if (isset($path) && $path != '') 
                        {
                            $spopCommand .= " " . $path;
                        }
                        
                        if (isset($_POST['p2']) && $_POST['p2'] != '') 
                        {
                            $spopCommand .= " " . $_POST['p2'];
                        }
                        
                        // stop any mpd playback					
                        $playBackCommands = array("play", "stop", "next", "prev", "goto", "add", "uplay", "uadd");
                        if (in_array($spopCommand, $playBackCommands))
                        {
                            $this->mpdService->sendMpdCommand("stop");
                        }
                        
                        echo json_encode($this->spotifyService->sendCommand($spopCommand));
                    }
                    break;
            }
        } 
        else 
        {
            echo json_encode(
                [
                'service'       => 'MPD DB INTERFACE',
                'disclaimer'    => 'INTERNAL USE ONLY!',
                'hosted_on' 	=> gethostname() . ":" . $_SERVER['SERVER_PORT']
                ]);
        }
    }
    
    public function command(Request $request)
    {
        $commandName = $request->input('cmd');
        
        if ($commandName) 
        {
            $sRawCommand = $commandName;
            $sSpopCommand = NULL;

            $stringSpopState = $this->spotifyService->getSpopState("CurrentState")['state'];

            if (strcmp($stringSpopState, 'play') == 0 || strcmp($stringSpopState, 'pause') == 0) 
            {
                // If spotify playback mode
                if (strcmp($sRawCommand, "previous") == 0) 
                {
                    $sSpopCommand = "prev";
                } 
                else if (strcmp($sRawCommand, "pause") == 0) 
                {
                    $sSpopCommand = "toggle";
                } 
                else if (strcmp(substr($sRawCommand,0,6), "random") == 0) 
                {
                    $sSpopCommand = "shuffle";
                } 
                else if (strcmp(substr($sRawCommand,0,6), "repeat") == 0) 
                {
                    $sSpopCommand = "repeat";
                } 
                else if (strcmp(substr($sRawCommand,0,6), "single") == 0 || strcmp(substr($sRawCommand,0,7), "consume") == 0) 
                {
                    // Ignore command since spop does not support
                    $sSpopCommand = "";
                } 
                else if (strcmp($sRawCommand, "play") == 0 || strcmp($sRawCommand, "next") == 0 || strcmp($sRawCommand, "stop") == 0 || strcmp(substr($sRawCommand,0,4), "seek") == 0) 
                {
                    $sSpopCommand = $sRawCommand;
                }
            }

            if (isset($sSpopCommand)) 
            {
                // If command is to be passed to spop
                if (strcmp($sSpopCommand,"") != 0) 
                {
                    echo json_encode($this->spotifyService->sendCommand($sSpopCommand));
                }
            } 
            else 
            {
                // Else pass command to MPD
                echo json_encode($this->mpdService->sendMpdCommand($sRawCommand));
            }
        }
    }
}
