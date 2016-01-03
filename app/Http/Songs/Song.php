<?php

namespace App\Http\Songs;

use App\Http\Services\ConnectionService;

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
}