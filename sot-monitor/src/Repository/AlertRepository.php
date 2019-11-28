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

    /**
     * @param int $limit
     * @return Alert[]
     */
    public function findRecent(int $limit): array
    {
        $sql = sprintf('SELECT * FROM alert ORDER BY timestamp DESC LIMIT %d;', $limit);
        $statement = $this->connection->prepare($sql);
        $statement->execute();

        return array_map(function(array $row) {
            return $this->parse($row);
        }, $statement->fetchAll());

    }

    public function findAllAfter(int $timestamp): array
    {
        $sql = 'SELECT * FROM alert WHERE timestamp > :timestamp ORDER BY timestamp DESC;';
        $statement = $this->connection->prepare($sql);
        $statement->execute([ 'timestamp' => $timestamp ]);

        return array_map(function(array $row) {
            return $this->parse($row);
        }, $statement->fetchAll());
    }

    private function parse(array $row): Alert
    {
        $type = $row['type'];
        $data = json_decode($row['data'], true);
        $timestamp = $row['timestamp'];
        $priority = $row['priority'];

        return new Alert($type, $data, $timestamp, $priority);
    }
}