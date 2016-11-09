<?php

namespace App\Http\Controllers;

use App\Volumio\Services\AlbumArtService;
use App\Volumio\Services\ConnectionService;
use App\Volumio\Services\MusicPlayerService;
use App\Volumio\Utils\ObjectConverterUtil;
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
        $query = $request->input('query');
        $searchType = $request->input('searchType');

        return $this->musicPlayerService->sendCommand($commandName, $serviceType, $song, $playlist, $query, $searchType);
    }
}
