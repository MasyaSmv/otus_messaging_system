<?php

require __DIR__ . '/bootstrap.php';

use Masyasmv\IoC\IoC;
use Masyasmv\Messaging\Http\Controller\MessageController;
use Masyasmv\Messaging\Service\GameServer;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// 1) Подключаемся к RabbitMQ (порт, который вы пробросили в docker-compose)
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel    = $connection->channel();

// 2) Объявляем exchange/queue/bind (если ещё не объявлены)
$channel->exchange_declare('game_messages', 'direct', false, true, false);
$channel->queue_declare   ('message_processor', false, true, false, false);
$channel->queue_bind      ('message_processor', 'game_messages', 'agent.msg');

// 3) Callback для обработки сообщений
$callback = static function(AMQPMessage $msg) {
    echo "[x] Received: ", $msg->getBody(), "\n";

    $payload = json_decode($msg->getBody(), true);
    if (!is_array($payload)) {
        echo "[!] Invalid JSON\n";
        $msg->ack();
        return;
    }

    /** @var GameServer $game */
    $game = IoC::Resolve('game.default');

    // Передаём в контроллер — он вызовет InterpretCommand и enqueue
    $controller = new MessageController();
    $result     = $controller->receive($payload, $game);
    echo "[>] Controller result: ", json_encode($result), "\n";

    // Выполняем очередь внутри GameServer
    $game->processQueue();

    $msg->ack();
};

$channel->basic_consume(
    'message_processor',
    '',
    false,
    false,
    false,
    false,
    $callback
);

echo "[*] Waiting for messages...\n";
while ($channel->is_consuming()) {
    $channel->wait();
}