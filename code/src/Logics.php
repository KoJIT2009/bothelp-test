<?php
declare(strict_types=1);

namespace App;

use Exception;

class Logics
{
    private array $users = [];

    private array $userChunks = [];

    private int $eventsMax = 10000;
    private int $eventCurrent = 1;

    private int $lastEventIdInDb;

    public function __construct(int $lastEventId)
    {
        $this->lastEventIdInDb = $lastEventId;
    }

    /**
     * Получаем id пользователя, если пользователи кончились, генерируем снова
     *
     * @return mixed
     */
    private function getUserId(): string
    {
        if (count($this->users) < 1) {
            $usersLocal = range(1, 1000);
            shuffle($usersLocal);

            $this->users = $usersLocal;
        }

        return (string)array_pop($this->users);
    }

    /**
     * Получем данные для отправки, либо null если лимит превышен
     *
     * @return array|null
     * @throws Exception
     */
    public function getData(): ?array
    {
        if (count($this->userChunks) < 1) {
            if ($this->eventCurrent > $this->eventsMax) {
                return null;
            }

            $this->calcChunks();
        }

        return array_pop($this->userChunks);
    }

    /**
     * Вычисляем чанки для рандомного пользователя
     *
     * @throws Exception
     */
    private function calcChunks(): void
    {
        $userId = $this->getUserId();

        $chunkCount = random_int(1, 10);
        $chunkRange = range(1, $chunkCount);

        $localChunks = [];

        foreach ($chunkRange as $element) {
            if ($this->eventCurrent > $this->eventsMax) {
                break;
            }

            $localChunks[] = [$userId, $this->getDbEventId()];

            $this->eventCurrent += 1;
        }

        $this->userChunks = array_reverse($localChunks);
    }

    /**
     * @return int
     */
    private function getDbEventId(): int
    {
        return ($this->lastEventIdInDb + $this->eventCurrent);
    }
}
