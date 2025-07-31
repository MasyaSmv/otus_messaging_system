<?php

namespace Masyasmv\Messaging\Http\Controller;

use Masyasmv\Messaging\Http\Publisher;

class PublishController
{
    /**
     * Публикует входящее сообщение в очередь и возвращает статус.
     *
     * @param array $payload  — распарсенный JSON из body
     * @return array
     */
    public function send(array $payload): array
    {
        $publisher = new Publisher();
        $publisher->publish($payload);

        return ['status' => 'published'];
    }
}