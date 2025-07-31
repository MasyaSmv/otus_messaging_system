<?php
declare(strict_types=1);

require __DIR__.'/../bootstrap.php';

use Masyasmv\IoC\Entity\Ship;
use Masyasmv\IoC\IoC;
use Masyasmv\Messaging\Service\GameServer;

// при сидировании мы МОЖЕМ создавать объекты домена
$registry = IoC::Resolve('game.registry');

/** @var GameServer $game */
$game = IoC::Resolve('game.default');

// «Нормальный» класс из предыдущей ДЗ
$ship = new Ship();
$game->addObject('ship-001', $ship);

$registry->register('battle-123', $game);
