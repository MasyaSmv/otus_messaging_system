<?php

namespace Masyasmv\Messaging\Command;

use Masyasmv\IoC\Contract\Movable;
use Masyasmv\OtusMacroCommands\Contract\CommandInterface;

/**
 * Перемещает объект на dx/dy.
 *
 * Требования к объекту-получателю:
 *   – реализует \Masyasmv\IoC\Contract\Movable
 */
final class MoveCommand implements CommandInterface
{
    public function __construct(
        private Movable $target,
        private int     $dx,
        private int     $dy,
    ) {}

    public function execute(object $subject = null): void
    {
        // Поддерживаем как оригинальный target, так и subject, если его передали
        $obj = $subject instanceof Movable ? $subject : $this->target;
        $obj->setPosition($this->dx, $this->dy);
        printf("[Ship] position ⇒ (%d, %d)\n", $this->dx, $this->dy);
    }
}