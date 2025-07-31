<?php

namespace Masyasmv\Messaging\Http;

use Exception;
use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Publisher
{
    private AbstractChannel $channel;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $conn = new AMQPStreamConnection('localhost', 5673, 'myuser', 'mypass');
        $this->channel = $conn->channel();

        // убедимся, что exchange существует
        $this->channel->exchange_declare('game_messages', 'direct', false, true, false);
    }

    /**
     * @param array $payload
     *
     * @return void
     */
    public function publish(array $payload): void
    {
        $msg = new AMQPMessage(json_encode($payload), ['delivery_mode' => 2]);
        $this->channel->basic_publish($msg, 'game_messages', 'agent.msg');
    }
}