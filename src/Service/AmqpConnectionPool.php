<?php

namespace Masyasmv\Messaging\Service;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Хранит одно соединение и канал для повторного использования
 * во всех Publisher-ах, снижая накладные расходы.
 */
final class AmqpConnectionPool
{
    private static ?AMQPStreamConnection $conn = null;
    private static ?AMQPChannel $ch = null;

    /**
     * @throws Exception
     */
    public static function channel(): AMQPChannel
    {
        if (!self::$conn || !self::$conn->isConnected()) {
            self::$conn = new AMQPStreamConnection('127.0.0.1', 5673, 'myuser', 'mypass');
            self::$ch = self::$conn->channel();
            self::$ch->exchange_declare('game_messages', 'direct', false, true, false);
        }
        return self::$ch;
    }

    /**
     * Закрываем канал и соединение на shutdown
     *
     * @throws Exception
     */
    public static function close(): void
    {
        self::$ch?->close();
        self::$conn?->close();
    }
}
