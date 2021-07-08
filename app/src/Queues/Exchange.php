<?php

namespace App\Queues;


class Exchange {
    /**
     * Копирует все сообщения которые поступают к нему во все очереди
     */
    const TYPE_FANOUT = 'fanout';

    /**
     * Используется, когда нужно доставить сообщение в определенные очереди
     */
    const TYPE_DIRECT = 'direct';

    /**
     * @param $exchange
     * @param string $type
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection
     */
    public static function declare($exchange, $type = self::TYPE_DIRECT) : \PhpAmqpLib\Connection\AMQPStreamConnection {
        $connection = ConnectionManager::getInstance();
        $channel = $connection->channel();
        $channel->exchange_declare($exchange, $type, false, false, false);
        $channel->close();

        return $connection;
    }
}