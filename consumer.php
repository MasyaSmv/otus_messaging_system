<?php

require __DIR__ . '/bootstrap.php';
require __DIR__.'/seeds/battles.php';

use Masyasmv\IoC\IoC;
use Masyasmv\Messaging\Domain\Command\InterpretCommand;
use Masyasmv\Messaging\Domain\Message\IncomingMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// 1) Подключаемся к RabbitMQ (порт, который вы пробросили в docker-compose)
$connection = new AMQPStreamConnection('localhost', 5673, 'myuser', 'mypass');
$channel = $connection->channel();

// 2) Объявляем exchange/queue/bind (если ещё не объявлены)
$channel->exchange_declare('game_messages', 'direct', false, true, false);
$channel->queue_declare('message_processor', false, true, false, false);
$channel->queue_bind('message_processor', 'game_messages', 'agent.msg');

echo "[*] Waiting for messages...\n";

// 3) Callback для обработки сообщений
$callback = static function (AMQPMessage $msg) {
    $data = json_decode($msg->getBody(), true);
    if (!is_array($data)) {
        $msg->ack();
        return;
    }

    // 3) Преобразуем в объект и исполняем команду
    $incoming = IncomingMessage::fromArray($data);
    /** @var InterpretCommand $cmd */
    $cmd = IoC::Resolve('interpret.command', $incoming);
    $cmd->execute();                 // ставит нужную OperationCommand в очередь игры
    IoC::Resolve('game.default')     // берём GameServer
    ->processQueue();             // и выполняем все накопленные команды

    $msg->ack();                     // подтверждаем сообщение
};

// 4) Запуск консьюмера
$channel->basic_consume('message_processor', '', false, false, false, false, $callback);
while ($channel->is_consuming()) {
    $channel->wait();
}