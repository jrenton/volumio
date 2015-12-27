<?php

namespace App\Http\Controllers;

use App\Http\Services\AlbumArtService;
use App\Http\Services\ConnectionService;
use App\Http\Services\MusicPlayerService;
use App\Http\Utils\ObjectConverterUtil;
use App\User;
use Illuminate\Http\Request;

class MusicPlayerController extends Controller
{
    protected $musicPlayerService;

    public function __construct(MusicPlayerService $musicPlayerService)
    {
        $this->musicPlayerService = $musicPlayerService;
    }
    
    public function index(Request $request)
    {
        $commandName = $request->input('cmd');
        $song = $request->input('song');
        $serviceType = ucfirst($request->input('serviceType'));
        $playlist = $request->input('playlist');
        
        return $this->musicPlayerService->sendCommand($commandName, $serviceType, $song, $playlist);
    }
}