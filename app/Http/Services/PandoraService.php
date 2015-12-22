<?php

namespace App\Http\Services;

use App\Http\Services\ConnectionService;

class PandoraService
{
	protected $connectionService;
	
	public function __construct(ConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
    }
    
    function openSocket($host, $portSpop) 
	{
		$sock = stream_socket_client('tcp://'.$host.':'.$portSpop.'', $errorno, $errorstr, 30 );
        $output = "";
	
		if ($sock) 
		{
            while(!feof($sock))
            {
                $response = fgets($sock);
	
                $output .= $response;
                
                if (strpos($response, "204") !== false || strpos($response, "103") !== false)
                {
                    break;
                }
            }
		}
        
		return $sock;
	}
    
    function sendCommand($sock, $cmd) 
	{
        $output = "";
        
		if ($sock) 
		{
			//$cmd = $cmd."\n";
            //dd($cmd);
			$status = fputs($sock, $cmd);
	
            //dd($status);
            $i = 0;
            //$output = stream_get_contents($sock);
            
            //fclose($sock);
            // while (($buffer = fgets($sock, 4096)) !== false) 
            // {
            //     $output .= $buffer;
            // }
			while(!feof($sock))
			{
				// fgets() may time out during the wait for response from commands like 'idle'.
				// This loop will keep reading until a response is received, or until the socket closes.
				$response = fgets($sock);
	
                $output .= $response;
                
                // if (!$response)
                // {
                //     break;
                // }
                // $i++;
                // if ($i == 4)
                // {
                //     dd($output);
                //     dd(strpos($response, "204"));
                // }
                if ($response)
                {
                    break;
                }
                
                preg_match("/\d{3}/", $response, $matches, PREG_OFFSET_CAPTURE, 3);
                
                if ($matches)
                {
                    break;
                }
                // if (strpos($response, "204") !== false || strpos($response, "103") !== false)
                // {
                //     break;
                // }
			}        
		}
        
        return $output;
	}
}
