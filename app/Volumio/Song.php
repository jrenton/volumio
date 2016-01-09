<?php

namespace App\Volumio;

use App\Volumio\Services\ConnectionService;

abstract class Song
{
    public $id;
    public $title;
    public $artist;
    public $album;
    public $playlistId;
    public $type;
    public $serviceType;
    public $state;
    public $queueNumber;
    public $playlistNumber;
    public $coverart;
    public $base64;
    public $elapsed;
    public $time;
    public $rating;
}