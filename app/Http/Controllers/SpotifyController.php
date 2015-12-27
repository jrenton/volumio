<?php

namespace App\Http\Controllers;

use App\Http\Services\AlbumArtService;
use App\Http\Services\ConnectionService;
use App\Http\Services\SpotifyService;
use App\User;
use Illuminate\Http\Request;

class SpotifyController extends Controller
{
    protected $connectionService;
    protected $albumArtService;
    protected $spopService;

    public function __construct(ConnectionService $connectionService, SpotifyService $spopService, AlbumArtService $albumArtService)
    {
        $this->connectionService = $connectionService;
        $this->albumArtService = $albumArtService;
        $this->spopService = $spopService;
    }

    function playerEngine(Request $request)
    {        
        $state = $request->input('state');
        
        // Get the current status array
        $status = $this->spopService->getSpopState("CurrentState");
    
        if ($state == $status['state']) 
        {
            // If the playback state is the same as specified in the ajax call
            // Wait until the status changes and then return new status
            $status = $this->spopService->getSpopState("NextState");
        }
        
        $this->spopService->closeSpopSocket();
        
        return json_encode($status);
    }
}
