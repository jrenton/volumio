<?php

namespace App\Volumio\Notifiers;

abstract class Notifier
{
    protected $socket;
    
    public function __construct($tcp, $type)
    {
        $context = new \ZMQContext();
        $this->socket = $context->getSocket(\ZMQ::SOCKET_PUSH, $type);
        $this->socket->connect($tcp);
    }
}