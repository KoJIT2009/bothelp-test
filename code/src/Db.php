<?php
declare(strict_types=1);

namespace App;

use PDO;

/**
 * Class Db
 *
 * @package App
 */
class Db
{
    private const DSN = 'pgsql:host=bothelp-db;port=5432;dbname=bhd-1;user=bhd-u;password=bhd-u-123';

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = new PDO(self::DSN);
    }

    /**
     * @param string $userId
     * @param string $eventId
     *
     * @return bool
     */
    public function insertTaskOrder(string $userId, string $eventId): bool
    {
        $sql = <<<SQL
INSERT INTO tasks_order (account_id, event_id) values (?, ?);
SQL;

        return $this->pdo->prepare($sql)->execute([$userId, $eventId]);
    }

    /**
     * @param string $userId
     *
     * @return mixed
     */
    public function getFirstUserEvent(string $userId) {
        $sql = <<<SQL
SELECT tasks_order.event_id
FROM tasks_order
LEFT JOIN events_executed ee on tasks_order.event_id = ee.event_id
WHERE ee.event_id is null and account_id = ?
ORDER BY tasks_order.event_id ASC
LIMIT 1
SQL;

        $smtp = $this->pdo->prepare($sql);
        $smtp->execute([$userId]);

        return $smtp->fetchColumn();
    }

    /**
     * @return mixed
     */
    public function getLastEventId() {
        $sql = <<<SQL
SELECT tasks_order.event_id
FROM tasks_order
ORDER BY tasks_order.event_id DESC
LIMIT 1
SQL;

        $smtp = $this->pdo->query($sql);

        return $smtp->fetchColumn();
    }

    /**
     * @param string $eventId
     *
     * @return bool
     */
    public function insertExecutedEvent(string $eventId): bool
    {
        $sql = <<<SQL
INSERT INTO events_executed (event_id) values (?);
SQL;

        return $this->pdo->prepare($sql)->execute([$eventId]);
    }
}
