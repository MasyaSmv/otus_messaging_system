<?php

namespace Masyasmv\Messaging\Http\Controller;

use Masyasmv\IoC\IoC;
use Masyasmv\Messaging\Domain\GameServerInterface;
use Masyasmv\Messaging\Domain\Message\IncomingMessage;
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
        // 1) конвертируем массив в объект сообщения
        $msg = IncomingMessage::fromArray($payload);

        // 2) резолвим InterpretCommand из IoC (вы регистрировали его в bootstrap)
        /** @var CommandInterface $interpretCmd */
        $interpretCmd = IoC::Resolve('interpret.command', $msg);

        // 3) выполняем, передавая игровой сервер
        $interpretCmd->execute($game);

        // 4) возвращаем простой статус
        return ['status' => 'ok'];
    }
}
