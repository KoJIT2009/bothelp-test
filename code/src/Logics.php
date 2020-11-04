<?php
declare(strict_types=1);

namespace App;

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

    private function getDbEventId()
    {
        return ($this->lastEventIdInDb + $this->eventCurrent);
    }
}
