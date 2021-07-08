<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use App\Queues\ConnectionManager;
use App\Queues\Exchange;

class DefaultController
{
    /**
     * @var String | null
     */
    private $queueName = null;

    /**
     * @var String | null
     */
    private $exchange = null;

    /**
     * @var String | null
     */
    private $routingKey = null;

    /**
     * @throws \Exception
     */
    private function initQueueParams() {
        $queueParams = ConnectionManager::getConfig('queue_example');

        $this->queueName = $queueParams['queue'];
        $this->exchange = $queueParams['exchange'];
        $this->routingKey = $queueParams['routing_key'];
    }

    /**
     * Запись сообщения в очередь
     * @return Response
     * @throws \Exception
     */
    public function index() {
        $number = random_int(0, 100);
        $message = new AMQPMessage($number);
        $this->initQueueParams();

        /**
         * Создаем очередь
         */
        $connection = ConnectionManager::getInstance();
        $channel = $connection->channel();
        $channel->queue_declare($this->queueName, false, false, false, false);

        /**
         * Отправляем сообщение в нашу точку доступа
         */
        $channel = Exchange::declare($this->exchange)->channel();
        $channel->basic_publish($message, $this->exchange, $this->routingKey);

        /**
         *  Сообщаем точке доступа, чтобы она отправила сообщение в очередь.
         */
        $channel->queue_bind($this->queueName, $this->exchange, $this->routingKey);

        $channel->close();
        $connection->close();

        return new Response("");
    }

    /**
     * Чтение сообщений из очереди
     * @return Response
     * @throws \Exception
     */
    public function receiver() {
        $this->initQueueParams();
        $connection = ConnectionManager::getInstance();
        $channel = $connection->channel();

        while ($message = $channel->basic_get($this->queueName)) {
            var_dump($message->delivery_info['routing_key']);
            var_dump($message->body);
            $channel->basic_ack($message->getDeliveryTag());
        }

        $channel->close();
        $connection->close();

        return new Response("");
    }

    /**
     * Пример чтения из очереди
     * Аналогично методу receiver, только в бесконечном цикле
     * @throws \ErrorException
     */
    public function receiverV1() {
        $queue = $this->queueName;

        $connection = ConnectionManager::getInstance();
        $channel = $connection->channel();

        $callback = function($message) {
            $info = $message->delivery_info['routing_key'] . " :". $message->body;
            var_dump($info);
            var_dump($message->body);
            die();
        };

        $noAck = true;
        $channel->basic_consume($queue, '', false, $noAck, false, false, $callback);


        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}