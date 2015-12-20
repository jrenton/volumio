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
        $spop = $this->spopService->openSpopSocket(DAEMONIP, 6602);
        
        if (!$spop) 
        {
            return "";
        } 
        
        $state = $request->input('state');
        
        // Get the current status array
        $status = $this->spopService->getSpopState($spop,"CurrentState");
    
        if ($state == $status['state']) 
        {
            // If the playback state is the same as specified in the ajax call
            // Wait until the status changes and then return new status
            $status = $this->spopService->getSpopState($spop, "NextState");
        } 
    
        // Return data in json format to ajax requester and close socket
        //header('Content-Type: application/json');
        
        $this->spopService->closeSpopSocket($spop);
        
        return json_encode($status);
    }
}
