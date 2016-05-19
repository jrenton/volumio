<?php

namespace App\Volumio\Notifiers;

use Illuminate\Database\DatabaseManager as DB;
use App\Volumio\Services\CurrentSongService;

class SongChangeNotifier extends Notifier
{
    protected $currentSongService;
    
    public function __construct(CurrentSongService $currentSongService)
    {
        parent::__construct("tcp://localhost:4500", "song change");
        $this->currentSongService = $currentSongService;
    }
    
    public function notify($song)
    {
        $this->socket->send(json_encode($song));
        $this->currentSongService->addSongToDb($song);
    }
}