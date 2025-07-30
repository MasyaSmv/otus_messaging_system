<?php

namespace Masyasmv\Messaging\Domain\Message;

use InvalidArgumentException;

final class IncomingMessage
{
    public function __construct(
        private readonly string $gameId,
        private readonly string $objectId,
        private readonly string $operationId,
        private readonly array $args,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string)($data['game_id'] ?? throw new InvalidArgumentException('game_id')),
            (string)($data['object_id'] ?? throw new InvalidArgumentException('object_id')),
            (string)($data['operation_id'] ?? throw new InvalidArgumentException('operation_id')),
            (array)($data['args'] ?? []),
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

    public function args(): array
    {
        return $this->args;
    }
}
