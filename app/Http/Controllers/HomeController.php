<?php

namespace App\Http\Controllers;

use App\Volumio\Spotify\SpotifyWebApi;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $spotifyWebApi;

    public function __construct(SpotifyWebApi $spotifyWebApi)
    {
        $this->spotifyWebApi = $spotifyWebApi;
    }
    
    public function index(Request $request)
    {
        $code = $request->input('code');
    
        if ($code) 
        {
            $this->spotifyWebApi->authenticate($code);
            
            return redirect("/");
        }
        
        return view('home');
    }
}
