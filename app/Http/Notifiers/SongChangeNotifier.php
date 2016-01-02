<?php

namespace App\Http\Notifiers;

class SongChangeNotifier extends Notifier
{    
    public function __construct()
    {
        parent::__construct("tcp://localhost:4500", "song change");
    }
    
    public function notify($song)
    {
        $this->socket->send(json_encode($song));
    }
}