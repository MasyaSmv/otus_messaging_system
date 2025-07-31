<?php
declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

use Masyasmv\IoC\IoC;
use Masyasmv\Messaging\Domain\Command\InterpretCommand;
use Masyasmv\Messaging\Domain\Message\IncomingMessage;
use Masyasmv\Messaging\Service\GameRegistry;
use Masyasmv\Messaging\Service\GameServer;
use Masyasmv\Messaging\Service\OperationResolver;

// 1. Сервисы
IoC::Resolve('IoC.Register','interpret.command',
    static fn(IncomingMessage $m) => new InterpretCommand($m))->Execute();

$registry = new GameRegistry();
IoC::Resolve('IoC.Register', 'game.registry', fn() => $registry)->Execute();

$defaultGame = new GameServer();
IoC::Resolve('IoC.Register', 'game.default', fn() => $defaultGame)->Execute();

// 2. OperationResolver из конфигурационного файла
$operations = require __DIR__.'/config/operations.php';
IoC::Resolve('IoC.Register','operation.resolver',
    static fn() => new OperationResolver($operations))->Execute();

register_shutdown_function([Masyasmv\Messaging\Service\AmqpConnectionPool::class, 'close']);

