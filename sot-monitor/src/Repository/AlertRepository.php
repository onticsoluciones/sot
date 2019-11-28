<?php

namespace Ontic\Sot\Monitor\Repository;

use Ontic\Sot\Monitor\Model\Alert;
use PDO;

class AlertRepository
{
    /** @var PDO */
    private $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(Alert $alert)
    {
        $sql = '
          INSERT INTO alert(type, data, timestamp, priority) 
          VALUES(:type, :data, :timestamp, :priority);';
        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'type' => $alert->getType(),
            'data' => json_encode($alert->getData()),
            'timestamp' => $alert->getTimestamp(),
            'priority' => $alert->getPriority()
        ]);
    }
}