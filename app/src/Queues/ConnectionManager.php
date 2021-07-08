<?php
namespace App\Queues;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ConnectionManager {

    protected static $instance;

    protected function __construct() { }

    protected function __clone() { }

    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    /**
     * @return AMQPStreamConnection
     */
    public static function getInstance(): AMQPStreamConnection {
        if (empty(self::$instance)) {
            $config = self::getConfig('rabbit_mq');
            $connection = new AMQPStreamConnection($config['host'], $config['port'], $config['user'], $config['password'], $config['vhost']);
            self::$instance = $connection;
        }

        return self::$instance;
    }

    public static function getConfig($key = false) {
        $configPath = dirname(__DIR__) . '/../config/dev.php';
        if (!file_exists($configPath)) {
            throw new \Exception("Не удалось найти файл конфигурации.");
        }

        $config = require $configPath;
        return $key ? $config[$key] : $config;
    }
}