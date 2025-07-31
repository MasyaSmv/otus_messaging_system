<?php

namespace Masyasmv\Messaging\Domain\Command;

use Masyasmv\IoC\IoC;
use Masyasmv\Messaging\Service\OperationResolver;
use Masyasmv\OtusMacroCommands\Contract\CommandInterface;
use Masyasmv\Messaging\Domain\GameServerInterface;
use Masyasmv\Messaging\Domain\Message\IncomingMessage;
use Masyasmv\Messaging\Service\GameRegistry;

final class InterpretCommand implements CommandInterface
{
    public function __construct(private IncomingMessage $message) {}

    /**
     * @param GameServerInterface $subject
     *
     * @return void
     */
    public function execute(object $subject = null): void
    {
        // 1) достаём из IoC регистры и резолвер
        /** @var GameRegistry $registry */
        $registry = IoC::Resolve('game.registry');
        /** @var OperationResolver $resolver */
        $resolver = IoC::Resolve('operation.resolver');

        // 2) получаем нужный GameServer по gameId
        $game = $registry->get($this->message->gameId());

        // 3) создаём конкретную команду операции
        $cmd = $resolver->resolve(
            $this->message->operationId(),
            $game->getObject($this->message->objectId()),
            $this->message->args()
        );

        // 4) ставим её в очередь игры
        $game->enqueue($cmd);
    }
}
