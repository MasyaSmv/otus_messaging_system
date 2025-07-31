<?php

namespace Tests\Unit;

use Masyasmv\IoC\IoC;
use Masyasmv\Messaging\Domain\Command\InterpretCommand;
use Masyasmv\Messaging\Domain\Message\IncomingMessage;
use Masyasmv\Messaging\Service\GameRegistry;
use Masyasmv\Messaging\Service\GameServer;
use Masyasmv\Messaging\Service\OperationResolver;
use Masyasmv\OtusMacroCommands\Contract\CommandInterface;
use PHPUnit\Framework\TestCase;

final class InterpretCommandTest extends TestCase
{
    public function testCreatesAndQueuesConcreteCommand(): void
    {
        // stub GameServer
        $game = $this->createMock(GameServer::class);
        $game->expects($this->once())->method('enqueue')
            ->with($this->isInstanceOf(CommandInterface::class));

        $registry = new GameRegistry();
        $registry->register('battle-1', $game);

        IoC::Resolve('IoC.Register', 'game.registry', fn() => $registry)->Execute();
        IoC::Resolve('IoC.Register', 'operation.resolver',
            fn() => new OperationResolver([
                'noop' => static fn() => $this->createMock(CommandInterface::class),
            ]))->Execute();

        $msg  = new IncomingMessage(1, 'battle-1', 'ship', 'noop', []);
        $cmd  = new InterpretCommand($msg);
        $cmd->execute();
    }
}