<?php

use Masyasmv\IoC\Contract\Movable;
use Masyasmv\Messaging\Command\MoveCommand;
use Masyasmv\OtusMacroCommands\Command\RotateCommand;
use Masyasmv\OtusMacroCommands\Contract\RotatorInterface;

/**
 * operation-id → фабрика команды
 *
 * Каждая фабрика обязана вернуть объект,
 * реализующий Contract\CommandInterface.
 */
return [
    'move' => static fn(Movable $t, array $a) => new MoveCommand(
        $t,
        (int)($a['dx'] ?? 0),
        (int)($a['dy'] ?? 0),
    ),

    'rotate' => static fn(RotatorInterface $t, array $a) => new RotateCommand(
        $t,
        (int)($a['angle'] ?? 0),
    ),
];
