<?php

namespace Masyasmv\Messaging\Http;

use Exception;
use Masyasmv\Messaging\Service\AmqpConnectionPool;
use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Publisher
{
    /**
     * @param array $payload
     *
     * @return void
     * @throws Exception
     */
    public function publish(array $payload): void
    {
        $msg = new AMQPMessage(json_encode($payload), [
            'content_type'  => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);

        AmqpConnectionPool::channel()->basic_publish($msg, 'game_messages', 'agent.msg');
    }
}