<?php

namespace App\Volumio\Pandora;

class PandoraSocket
{
    static $sock;
        
    static function getInstance()
    {
        if (!static::$sock)
        {
            static::$sock = stream_socket_client('tcp://localhost:4445', $errorno, $errorstr, 30 );
            stream_set_timeout(static::$sock, 0, 200000);
			fputs(static::$sock, "user admin admin\n");
	
			while(!feof(static::$sock))
			{
				// fgets() may time out during the wait for response from commands like 'idle'.
				// This loop will keep reading until a response is received, or until the socket closes.
				$output = fgets(static::$sock);
                
                //echo $output;
                
                if (!$output)
                {
                    break;
                }
			}
        }
        
        return static::$sock;
    }
}