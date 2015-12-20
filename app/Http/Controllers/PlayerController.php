<?php

namespace App\Http\Controllers;

use App\Http\Services\AlbumArtService;
use App\Http\Services\ConnectionService;
use App\Http\Services\SpotifyService;
use App\Http\Services\MpdService;
use App\User;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    protected $connectionService;
    protected $albumArtService;
    protected $spopService;
    protected $mpdService;

    public function __construct(ConnectionService $connectionService, SpotifyService $spopService, MpdService $mpdService, AlbumArtService $albumArtService)
    {
        $this->connectionService = $connectionService;
        $this->albumArtService = $albumArtService;
        $this->spopService = $spopService;
        $this->mpdService = $mpdService;
    }
    
    function sendCommand(Request $request)
    {
        $mpd = $this->mpdService->openMpdSocket(DAEMONIP, 6600);        
        $spop = $this->spopService->openSpopSocket(DAEMONIP, 6602);
        
        $commandName = $request->input('cmd');
        
        if ($commandName) 
        {
            if ( !$mpd ) 
            {
                return json_encode(['error' => 'Error Connecting to MPD daemon']);	
            }
            
            $path = $request->input('path');
            $p2 = $request->input('p2');
            
            switch ($commandName) 
            {
                case 'filepath':
                    if ($path) 
                    {
                        if ($spop && strcmp(substr($path,0,7),"SPOTIFY") == 0) 
                        {
                            $arraySpopSearchResults = $this->spopService->querySpopDB($spop, 'filepath', $path);
                            echo json_encode($arraySpopSearchResults);
                        } 
                        else 
                        {
                            $arrayMpdSearchResults = $this->mpdService->searchDB($mpd,'filepath',$path);
                            echo json_encode($arrayMpdSearchResults);
                        }	
                    } 
                    else 
                    {
                        $arraySearchResults = $this->mpdService->searchDB($mpd, 'filepath');
        
                        if ($spop) 
                        {
                            $arraySpopSearchResults = $this->spopService->querySpopDB($spop, 'filepath', '');
                            $arraySearchResults = array_merge($arraySearchResults, $arraySpopSearchResults);
                        }
        
                        echo json_encode($arraySearchResults);
                    }
        
                    break;
        
                case 'playlist':
                    echo json_encode($this->mpdService->getPlayQueue($mpd));
                    break;
        
                case 'add':
                    if (isset($path) && $path != '') 
                    {
                        echo json_encode($this->mpdService->addQueue($mpd,$path));
                    }
                    break;
                
                case 'addplay':
                    if (isset($path) && $path != '') 
                    {
                        $status = $this->connectionService->_parseStatusResponse($this->mpdService->MpdStatus($mpd));
                        $pos = $status['playlistlength'] ;
                        $this->mpdService->addQueue($mpd, $path);
                        $this->mpdService->sendMpdCommand($mpd, 'play ' . $pos);
                        echo json_encode($this->mpdService->readMpdResponse($mpd));
                    }
                    break;
        
                case 'addreplaceplay':
                    if (isset($path) && $path != '') 
                    {
                        $this->mpdService->sendMpdCommand($mpd,'clear');
                        $this->mpdService->addQueue($mpd,$path);
                        $this->mpdService->sendMpdCommand($mpd,'play');
                        echo json_encode($this->mpdService->readMpdResponse($mpd));
                    }
                    break;
                
                case 'update':
                    if (isset($path) && $path != '') 
                    {
                        $this->mpdService->sendMpdCommand($mpd,"update \"".html_entity_decode($path)."\"");
                        echo json_encode($this->mpdService->readMpdResponse($mpd));
                    }
                    break;
                
                case 'trackremove':
                    if (isset($_GET['songid']) && $_GET['songid'] != '') 
                    {
                        echo json_encode($this->mpdService->remTrackQueue($mpd,$_GET['songid']));
                    }
                    break;
        
                case 'savepl':
                    if (isset($_GET['plname']) && $_GET['plname'] != '')
                    {
                        $this->mpdService->sendMpdCommand($mpd,"rm \"".html_entity_decode($_GET['plname'])."\"");
                        $this->mpdService->sendMpdCommand($mpd,"save \"".html_entity_decode($_GET['plname'])."\"");
                        echo json_encode($this->mpdService->readMpdResponse($mpd));
                    }
                    break;
                
                case 'search':
                    if (isset($_POST['query']) && $_POST['query'] != '' && isset($_GET['querytype']) && $_GET['querytype'] != '') 
                    {
                        $arraySearchResults = $this->mpdService->searchDB($mpd,$_GET['querytype'],$_POST['query']);
        
                        if ($spop) 
                        {
                            $arraySpopSearchResults = $this->spopService->querySpopDB($spop, 'file', $_POST['query']);
                            $arraySearchResults = array_merge($arraySearchResults, $arraySpopSearchResults);
                        }
        
                        echo json_encode($arraySearchResults);
                    }
        
                    break;
        
                case 'loadlib':
                    echo $this->mpdService->loadAllLib($mpd);
                    break;
        
                case 'playall':
                    if (isset($path) && $path != '') 
                    {
                        echo json_encode($this->mpdService->playAll($mpd,$path));
                    }
                    break;
        
                case 'addall':
                    if (isset($path) && $path != '') 
                    {
                        echo json_encode($this->mpdService->enqueueAll($mpd,$path));
                    }
                    break;	
                case 'spop-playplaylistindex':
                    if (isset($path) && $path != '') 
                    {
                        $sSpopPlaylistIndex = end(explode("@", $path));
                        $this->mpdService->sendMpdCommand($mpd,'stop');
                        echo $this->spopService->sendSpopCommand($spop, "play " . $sSpopPlaylistIndex);
                    }
                    break;
        
                case 'spop-addplaylistindex':
                    if (isset($path) && $path != '') 
                    {
                        $sSpopPlaylistIndex = end(explode("@", $path));
                        echo $this->spopService->sendSpopCommand($spop, "add " . $sSpopPlaylistIndex);
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
                            $this->mpdService->sendMpdCommand($mpd, "stop");
                        }
                        
                        echo json_encode($this->spopService->sendSpopCommand($spop, $spopCommand));
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
        
        if ($mpd) 
        {
            $this->mpdService->closeMpdSocket($mpd);
        }
        
        if ($spop) 
        {
            $this->spopService->closeSpopSocket($spop);
        }
    }
}
