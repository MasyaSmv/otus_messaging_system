<?php

namespace Masyasmv\Messaging\Service;

use InvalidArgumentException;
use Masyasmv\IoC\IoC;
use Masyasmv\OtusMacroCommands\Contract\CommandInterface;

final class OperationResolver
{
    /** @var array<string,string>  operationId → класс команды */
    private array $operations;

    /**
     * @param array<string,string> $operations
     *   Например:
     *     [
     *       'move'   => \Masyasmv\OtusMovingObjects\Command\MoveCommand::class,
     *       'rotate' => \Masyasmv\OtusMovingObjects\Command\RotateCommand::class,
     *       // …
     *     ]
     */
    public function __construct(array $operations)
    {
        $this->operations = $operations;
    }

    /**
     * Создаёт и возвращает конкретную команду для операции.
     *
     * @param string $operationId ключ операции из входящего сообщения
     * @param object $target игровой объект (или сервер)
     * @param array $args параметры операции
     *
     * @return CommandInterface
     * @throws InvalidArgumentException
     */
    public function resolve(string $operationId, object $target, array $args): CommandInterface
    {
        if (!isset($this->operations[$operationId])) {
            throw new InvalidArgumentException("Операция «{$operationId}» не зарегистрирована");
        }

        $commandClass = $this->operations[$operationId];

        /** @var CommandInterface $cmd */
        $cmd = IoC::Resolve(
            $commandClass,
            $target,
            ...array_values($args),
        );

        return $cmd;
    }
}
