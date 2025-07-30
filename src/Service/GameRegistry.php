<?php

namespace Masyasmv\Messaging\Service;

use RuntimeException;

final class GameRegistry
{
    /** @var array<string, GameServer> */
    private array $games = [];

    public function register(string $gameId, GameServer $game): void
    {
        $this->games[$gameId] = $game;
    }

    public function get(string $gameId): GameServer
    {
        return $this->games[$gameId]
            ?? throw new RuntimeException("Game $gameId not found");
    }
}
