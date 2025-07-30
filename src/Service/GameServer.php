<?php

namespace Masyasmv\Messaging\Service;

use Masyasmv\Messaging\Domain\GameServerInterface;
use Masyasmv\OtusMacroCommands\Contract\CommandInterface;
use RuntimeException;

class GameServer implements GameServerInterface
{
    /** @var object[] */
    private array $objects = [];

    private \SplQueue $queue;

    public function __construct()
    {
        $this->queue = new \SplQueue();
    }

    public function addObject(string $id, object $obj): void
    {
        $this->objects[$id] = $obj;
    }

    public function getObject(string $id): object
    {
        if (! isset($this->objects[$id])) {
            throw new RuntimeException("GameServer: объект с id «{$id}» не найден");
        }
        return $this->objects[$id];
    }

    public function enqueue(CommandInterface $command): void
    {
        $this->queue->enqueue($command);
    }

    /**
     * Проходим по очереди и для каждого $cmd вызываем execute($this).
     * Все ваши команды (InterpretCommand и OperationCommand) должны
     * иметь сигнатуру public function execute(GameServerInterface $game): void
     */
    public function processQueue(): void
    {
        while (! $this->queue->isEmpty()) {
            /** @var CommandInterface $cmd */
            $cmd = $this->queue->dequeue();
            $cmd->execute($this);
        }
    }
}
