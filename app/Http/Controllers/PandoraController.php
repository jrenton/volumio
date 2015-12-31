<?php

namespace App\Http\Controllers;

use App\Http\Services\AlbumArtService;
use App\Http\Services\ConnectionService;
use App\Http\Services\PandoraService;
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