<?php

namespace App\Http\Utils;

class ServiceUtils 
{
    public static function getClass($serviceName, $connectionService) 
    {
        $serviceNamespace = "App\\Http\\Services\\";
        $commandClassName = $serviceNamespace . $serviceName . "Service";
        
        if (!class_exists($commandClassName)) 
        {
            throw new \Exception("Music player service does not exist for type " . $serviceName);
        }
                
        $serviceClass = new $commandClassName($connectionService);
        
        return $serviceClass;
    }
}
