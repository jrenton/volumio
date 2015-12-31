<?php
namespace App\Http\WebSockets;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use App\Http\Services\PandoraService;

class Pusher implements WampServerInterface 
{
    protected $clients;
    protected $pandoraService;
    private $lastMessageSentTime;

    public function __construct(PandoraService $pandoraService) 
    {
        $this->clients = new \SplObjectStorage;
        $this->pandoraService = $pandoraService;
        $this->lastMessageSentTime = microtime();
    }

    public function onOpen(ConnectionInterface $conn) 
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }
    
    public function onSubscribe(ConnectionInterface $conn, $topic) 
    {
        $this->clients->attach($conn);
        // array_push($this->subscribedTopics, $topic);
    }

    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onReceiveMessage($entry) 
    {
        // $entryData = json_decode($entry, true);

        // // If the lookup topic object isn't set there is no one to publish to
        // if (!array_key_exists($entryData['category'], $this->subscribedTopics)) {
        //     return;
        // }

        // $topic = $this->subscribedTopics[0];

        // // re-send the data to all the clients subscribed to that category
        // $messageSentTime = microtime();
        
        // $timeSinceLastMessage = $messageSentTime - $this->lastMessageSentTime;
        // if ($timeSinceLastMessage) 
        // {
        echo "\nReceived message:\n";
        echo $entry;
        
        $parsedMessage = $this->pandoraService->parseMessage($entry);
        
        if ($parsedMessage)
        {
            foreach($this->clients as $client)
            {
                $client->send(json_encode($parsedMessage));            
            }
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