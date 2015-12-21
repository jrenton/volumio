<?php

namespace App\Http\Services;

class AlbumArtService
{
	function getBase64AlbumArt($file) 
    {
        $fileName = "/mnt/" . str_replace("\\", "/", $file);
        $basePath = substr($fileName, 0, strrpos($fileName, '/') + 1);
    
        return $this->findAlbumArtInFolder($basePath);
    }
    
    function findAlbumArtInFolder($path)
    {
        $possibleAlbumArtFiles = array("Folder.jpg", "AlbumArt*.*");
        $filesInDirectory = scandir($path);
        
        foreach ($possibleAlbumArtFiles as $file)
        {
            $albumArtMatches = preg_grep("/" . $file . "/", $filesInDirectory);
            
            foreach ($albumArtMatches as $albumArtFile)
            {
                $pathToCoverPhoto = $path . $albumArtFile;
                $data = file_get_contents($pathToCoverPhoto);
                
                if($data) 
                {
                    return base64_encode($data);
                }
            }
        }
        
        //find album art on the internet
        
        return "";
    }
}