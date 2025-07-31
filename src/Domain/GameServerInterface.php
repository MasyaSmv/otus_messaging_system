<?php

namespace Masyasmv\Messaging\Domain;

use Masyasmv\OtusMacroCommands\Contract\CommandInterface;

interface GameServerInterface
{
    public function getObject(string $id): object;
    public function enqueue(CommandInterface $command): void;
}