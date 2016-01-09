<?php

namespace App\Http\Controllers;

use App\Volumio\Services\AlbumArtService;
use App\Volumio\Services\ConnectionService;
use App\Volumio\Pandora\PandoraService;
use Illuminate\Http\Request;

class PandoraController extends Controller
{
    protected $connectionService;
    protected $albumArtService;
    protected $pandoraService;

    public function __construct(ConnectionService $connectionService, PandoraService $pandoraService, AlbumArtService $albumArtService)
    {
        $this->connectionService = $connectionService;
        $this->albumArtService = $albumArtService;
        $this->pandoraService = $pandoraService;
    }
    
    public function playerEngine(Request $request)
    {
    }
}