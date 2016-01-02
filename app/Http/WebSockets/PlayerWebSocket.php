<?php
namespace App\Http\WebSockets;

use Ratchet\Wamp\WampServerInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class PlayerWebSocket implements WampServerInterface 
{
    protected $clients;

    public function __construct() 
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) 
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
    }
    
    public function onSubscribe(ConnectionInterface $conn, $topic) 
    {
        $this->clients->attach($conn);
    }

    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onReceiveMessage($message) 
    {
        echo "\nReceived message:\n";
        echo $message;
        
        foreach($this->clients as $client)
        {
            $client->send($message);            
        }
    }
        
    public function onUnSubscribe(ConnectionInterface $conn, $topic) 
    {
    }
    
    public function onClose(ConnectionInterface $conn) 
    {
    }
    
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) 
    {
        // In this application if clients send data it's because the user hacked around in console
        // $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        // echo "Published";
        // In this application if clients send data it's because the user hacked around in console
        //$conn->close();
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) 
    {
    }
}
