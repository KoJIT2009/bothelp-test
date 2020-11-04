<?php
declare(strict_types=1);

namespace App;

use ErrorException;
use Exception;
use JsonException;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Class Main
 *
 * @package App
 */
class Main
{
    private $db;
    private $queue;

    public function __construct()
    {
        $this->db = new Db();
        $this->queue = new Queue();
    }

    /**
     * Генерируем сообщения
     *
     * @throws JsonException|ErrorException
     */
    public function generate(): void
    {
        $logics = new Logics($this->lastEventId());

        while ($data = $logics->getData()) {
            $this->handleGeneratedData($data);
        }
    }

    /**
     * @param array $data
     *
     * @throws ErrorException
     * @throws JsonException
     */
    private function handleGeneratedData(array $data): void
    {
        try {
            $this->db->getPdo()->beginTransaction();


            $isAdd = $this->db->insertTaskOrder((string)$data[0], (string)$data[1]);

            if (false === $isAdd) {
                throw new ErrorException('Ошибка добавления');
            }

            $this->queue->publishMessage(json_encode($data, JSON_THROW_ON_ERROR));


            $this->db->getPdo()->commit();
        } catch (Exception $exception) {
            $this->db->getPdo()->rollBack();

            throw $exception;
        }
    }

    /**
     * Обработка сообщений
     *
     * @param $msg
     *
     * @throws JsonException
     */
    public function consume($msg): void
    {
        /** @var AMQPChannel $channel */
        $channel = $msg->delivery_info['channel'];

        $data = json_decode($msg->body, true, 512, JSON_THROW_ON_ERROR);

        $firstEvent = $this->db->getFirstUserEvent($data[0]);

        try {
            if ((string)$data[1] !== (string)$firstEvent) {
                throw new ErrorException('Ошибка очередности');
            }

            sleep(1);

            $isAdd = $this->db->insertExecutedEvent((string)$data[1]);

            if (false === $isAdd) {
                throw new ErrorException('Ошибка добавления');
            }

            $channel->basic_ack($msg->delivery_info['delivery_tag']);

            $this->writeToLog($data);
        } catch (ErrorException $exception) {
            $channel->basic_nack($msg->delivery_info['delivery_tag'], false, true);
        }
    }

    /**
     * Id послднго записанного собыли, либо 0
     *
     * @return int
     */
    private function lastEventId(): int
    {
        $lastEventId = $this->db->getLastEventId();

        if (false === $lastEventId) {
            $lastEventId = 0;
        }

        return (int)$lastEventId;
    }

    /**
     * Запись в файл лога
     *
     * @param array $data
     */
    private function writeToLog(array $data): void
    {
        $text = "account_id: $data[0], event_id: $data[1]";

        file_put_contents('/var/www/html/output/logs.txt', $text . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
