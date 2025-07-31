<?php

namespace Masyasmv\Messaging\Http\Controller;

use Masyasmv\IoC\IoC;
use Masyasmv\Messaging\Domain\GameServerInterface;
use Masyasmv\Messaging\Domain\Message\IncomingMessage;
use Masyasmv\Messaging\Http\Publisher;
use Masyasmv\OtusMacroCommands\Contract\CommandInterface;

final class MessageController
{
    /**
     * @param array $payload — распарсенный JSON из body
     * @param GameServerInterface $game — игровой сервер
     *
     * @return array — payload для JSON-ответа
     */
    public function receive(array $payload, GameServerInterface $game): array
    {
        // 1) Сначала публикуем в RabbitMQ
        $publisher = new Publisher();
        $publisher->publish($payload);

        // 2) Возвращаем клиенту, что сообщение ушло в очередь
        return ['status' => 'published'];
    }
}
