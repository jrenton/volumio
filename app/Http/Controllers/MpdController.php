<?php

namespace App\Http\Controllers;

use App\Http\Services\AlbumArtService;
use App\Http\Services\ConnectionService;
use App\Http\Services\MpdService;
use App\User;
use Illuminate\Http\Request;

class MpdController extends Controller
{
    protected $connectionService;
    protected $albumArtService;
    protected $mpdService;

    public function __construct(ConnectionService $connectionService, MpdService $mpdService, AlbumArtService $albumArtService)
    {
        $this->connectionService = $connectionService;
        $this->albumArtService = $albumArtService;
        $this->mpdService = $mpdService;
    }
    
    public function playerEngine(Request $request)
    {
        $mpd = $this->mpdService->openMpdSocket(DAEMONIP, 6600) ;
        //$spop = $this->connectionService->openSpopSocket(DAEMONIP, 6602);
        $state = $request->input('state');
        $db = 'sqlite:'.$_SERVER['DOCUMENT_ROOT'].'/db/player.db';
        
        $this->connectionService->playerSession('open', $db, '', ''); 

        if (!$mpd) 
        {
            return 'Error Connecting MPD Daemon';
        }
        
        // fetch MPD status
        $mpdStatus = $this->mpdService->MpdStatus($mpd);
        $status = array_merge(...$mpdStatus);
        // check for CMediaFix
        if ($request->session()->get('cmediafix') == 1) 
        {
            $request->session()->put('lastbitdepth', $status['audio']);
        }
        
        // check for Ramplay
        if ($request->session()->get('ramplay') == 1) 
        {
            // record "lastsongid" in PHP SESSION
            $request->session()->put('lastsongid', $status['songid']);
    
            // Control for cancelling ramplay
                // if (!rp_checkPLid($request->session()->get('lastsongid'),$mpd)) {
                // rp_deleteFile($request->session()->get('lastsongid'),$mpd);
                // }
    
            // feth next song and put in SESSION
            $request->session()->put('nextsongid', $status['nextsongid']);
        }
        
        // register player STATE in SESSION
        $request->session()->put('state', $status['state']);
    
        // Unlock SESSION file
        session_write_close();
    
        // -----  check and compare GUI state with Backend state  ----  //
        if ($state == $status['state']) 
        {
            // If the playback state is the same as specified in the ajax call
            // Wait until the status changes and then return new status
            $status = $this->mpdService->monitorMpdState($mpd);
        } 
        // -----  check and compare GUI state with Backend state  ----  //
    
        if (array_key_exists("song", $status))
        {
           $curTrack = $this->mpdService->getTrackInfo($mpd, $status['song']);
        
            foreach($curTrack[0] as $key => $value)
            {
                if($key == "Name") 
                {
                    $key = "Title";
                }
                
                $status[$key] = $value;
            } 
            if (strpos($status["file"], "http://") === false)
            {
                $status['base64'] = $this->albumArtService->getBase64AlbumArt($status["file"]);                
            }
            
            if (isset($status['Title'])) 
            {
                if (array_key_exists("Artist", $status))
                {
                    $status['currentartist'] = $status['Artist'];                    
                }
                if (array_key_exists("Title", $status))
                {
                    $status['currentsong'] = $status['Title'];
                }
                if (array_key_exists("Album", $status))
                {
                    $status['currentalbum'] = $status['Album'];
                }
                
                $status['fileext'] = $this->connectionService->parseFileStr($status['file'],'.');
            } 
            else 
            {
                $path = $this->connectionService->parseFileStr($curTrack[0]['file'],'/');
                $status['fileext'] = $this->connectionService->parseFileStr($curTrack[0]['file'],'.');
                $status['currentartist'] = "";
                $status['currentsong'] = $song;
                $status['currentalbum'] = "path: ".$path;
            }
        }
        
        // CMediaFix
        if ($request->session()->get('cmediafix') == 1 && $status['state'] == 'play' ) 
        {
            $status['lastbitdepth'] = $request->session()->get('lastbitdepth');
    
            if ($request->session()->get('lastbitdepth') != $status['audio']) 
            {
                $this->mpdService->sendMpdCommand($mpd,'cmediafix');
            }
        }
        
        // Ramplay
        if ($request->session()->get('ramplay') == 1) 
        {
            // set consume mode ON
            // if ($status['consume'] == 0) {
            // sendMpdCommand($mpd,'consume 1');
            // $status['consume'] = 1;
            // }
    
            // Copy the text from /dev/shm
            $path = $this->mpdService->rp_copyFile($status['nextsongid'],$mpd);
    
            // Update MPD ramplay location
            $this->mpdService->rp_updateFolder($mpd);
    
            // Launch add/play song
            $this->mpdService->rp_addPlay($path, $mpd, $status['playlistlength']);
        }
    
        // JSON response for GUI
        // header('Content-Type: application/json');
        // echo json_encode($status);
            
        $this->mpdService->closeMpdSocket($mpd);
        
        return json_encode($status);
    }
}
