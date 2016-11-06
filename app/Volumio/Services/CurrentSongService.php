<?php

namespace App\Volumio\Services;

use App\Volumio\Utils\ObjectConverterUtil;

class CurrentSongService
{
    protected $db;
    private $properties;

    public function __construct()
    {
        $this->db = app('db');
        $this->properties = array_keys(get_class_vars("App\Volumio\Song"));
    }
    
    public function getCurrentSong()
    {
        $song = [];
        $keyValues = $this->db->select("select * from current_song");

        if (is_object($keyValues))
        {
            $keyValues = json_decode(json_encode($keyValues), true);
        }

        foreach($keyValues as $keyValue)
        {
            $song[$keyValue["k"]] = $keyValue["v"];
        }
        
        return $song;
    }
    
    public function updateElapsed($elapsed)
    {
        $this->db->update("update current_song set v = '" . $elapsed . "' where k = 'elapsed'"); 
    }
    
    public function updateTime($time)
    {
        $this->db->update("update current_song set v = '" . $time . "' where k = 'time'"); 
    }
    
    public function addSongToDb($song)
    {
        if (!is_array($song))
        {
            $song = (array) $song;    
        }
        
        foreach ($this->properties as $property)
        {
            if (array_key_exists($property, $song))
            {
                $parsedValue = str_replace("'", "''", $song[$property]);

                if ($parsedValue === NULL || $parsedValue === "") {
                    continue;
                }
                
                $this->db->update("update current_song set v = '" . $parsedValue . "' where k = '$property'");
            }
        }
    }
}