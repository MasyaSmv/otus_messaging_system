<?php

declare(strict_types = 1);
require __DIR__ . '/vendor/autoload.php';

use Masyasmv\IoC\IoC;
use Masyasmv\IoC\Service\MoveCommand;
use Masyasmv\IoC\Service\RotateCommand;
use Masyasmv\Messaging\Domain\Command\InterpretCommand;
use Masyasmv\Messaging\Domain\Message\IncomingMessage;
use Masyasmv\Messaging\Service\GameRegistry;
use Masyasmv\Messaging\Service\GameServer;
use Masyasmv\Messaging\Service\OperationResolver;

// 1) Регистрация InterpretCommand
IoC::Resolve(
    'IoC.Register',
    'interpret.command',
    static fn(IncomingMessage $msg) => new InterpretCommand($msg),
)->Execute();

// 2) GameRegistry
IoC::Resolve('IoC.Register', 'game.registry', static fn() => new GameRegistry())->Execute();

// 3) GameServer
IoC::Resolve('IoC.Register', 'game.default', static fn() => new GameServer())->Execute();

// 4) OperationResolver
IoC::Resolve(
    'IoC.Register',
    'operation.resolver',
    static fn() => new OperationResolver([
        'move' => MoveCommand::class,
        'rotate' => RotateCommand::class,
    ]),
)->Execute();

// 5) Привязываем объекты к серверу и сам сервер в реестр
/** @var GameRegistry $registry */
$registry = IoC::Resolve('game.registry');
/** @var GameServer $game */
$game = IoC::Resolve('game.default');

// Пример: регистрируем игровой объект с id 'ship-001'.
// Здесь вы можете передать ваш реальный объект типа SpaceShip.
$game->addObject(
    'ship-001',
    new class {
        public function move(int $speed): void
        {
            echo "[Ship] Moving at speed $speed\n";
        }
    },
);

$registry->register('battle-123', $game);