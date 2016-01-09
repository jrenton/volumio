<?php

namespace App\Volumio\Utils;

use App\Volumio\Notifiers\SongChangeNotifier;

class ServiceUtils 
{
    public static function getClass($serviceName, $connectionService) 
    {
        $commandClassName = "App\\Volumio\\$serviceName\\$serviceName" . "Service";
        
        if (!class_exists($commandClassName)) 
        {
            throw new \Exception("Music player service does not exist for type " . $serviceName);
        }
        
        return new $commandClassName(app('App\Volumio\Services\ConnectionService'), app('App\Volumio\Notifiers\SongChangeNotifier'));
    }
}
