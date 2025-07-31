<?php

namespace Masyasmv\Messaging\Domain\Message;

final class IncomingMessage
{
    public function __construct(
        private int $version,
        private string $gameId,
        private string $objectId,
        private string $operationId,
        private array $args = []
    ) {
    }

    public static function fromArray(array $src): self
    {
        return new self(
            (int)($src['version'] ?? 1),
            (string)$src['game_id'],
            (string)$src['object_id'],
            (string)$src['operation_id'],
            (array)($src['args'] ?? []),
        );
    }

    public function gameId(): string
    {
        return $this->gameId;
    }

    public function objectId(): string
    {
        return $this->objectId;
    }

    public function operationId(): string
    {
        return $this->operationId;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function args(): array
    {
        return $this->args;
    }
}
