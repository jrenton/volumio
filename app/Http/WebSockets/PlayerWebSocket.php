<?php
namespace App\Http\WebSockets;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Http\Services\PandoraService;

class PlayerWebSocket implements MessageComponentInterface 
{
    protected $clients;
    protected $pandoraService;

    public function __construct(PandoraService $pandoraService) 
    {
        $this->clients = new \SplObjectStorage;
        $this->pandoraService = $pandoraService;
    }

    public function onOpen(ConnectionInterface $conn) 
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) 
    {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $response = $this->pandoraService->getResponse($msg);
        foreach ($this->clients as $client) 
        {
            $client->send(json_encode($response));
        }
    }

    public function onClose(ConnectionInterface $conn) 
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) 
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
