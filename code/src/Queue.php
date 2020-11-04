<?php
declare(strict_types=1);

namespace App;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class Queue
 *
 * @package App
 */
class Queue
{
    private const TASK_QUEUE = 'task_queue';

    /**
     * @var AMQPStreamConnection
     */
    private AMQPStreamConnection $connection;

    /**
     * @var AMQPChannel
     */
    private AMQPChannel $channel;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection('bothelp-rabbitmq', 5672, 'default', 'default');
        $this->channel = $this->connection->channel();
    }

    /**
     * @param string $data
     */
    public function publishMessage(string $data): void
    {
        $this->channel->queue_declare(self::TASK_QUEUE, false, true, false, false);

        $msg = new AMQPMessage($data, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $this->channel->basic_publish($msg, '', self::TASK_QUEUE);
    }

    /**
     * @param callable $callback
     *
     * @throws \ErrorException
     */
    public function consumeMessages(callable $callback): void
    {
        $this->channel->basic_qos(null, 10, null);
        $this->channel->basic_consume('task_queue', '', false, false, false, false, $callback);

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
