<?php

namespace Masyasmv\Messaging\Service;

use Closure;
use InvalidArgumentException;
use Masyasmv\OtusMacroCommands\Contract\CommandInterface;

final class OperationResolver
{
    /** @var array<string,string>  operationId → класс команды */
    private array $operations;

    /**
     * @param array<string, callable|class-string> $operations
     *         ключ operationId → фабрика-callable **или** FQCN команды
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
            throw new InvalidArgumentException("Operation «{$operationId}» not supported");
        }

        $factory = $this->operations[$operationId];

        if ($factory instanceof Closure) {
            /** @var CommandInterface $cmd */
            $cmd = $factory($target, $args);
        } else {
            // если у вас просто FQCN и args идут [dx, dy]
            $cmd = new $factory($target, ...array_values($args));
        }

        return $cmd;
    }
}
