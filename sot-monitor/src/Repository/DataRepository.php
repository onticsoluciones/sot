<?php

namespace Ontic\Sot\Monitor\Repository;

use PDO;

class DataRepository
{
    /** @var PDO */
    private $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function get(string $key, string $default): string
    {
        $sql = 'SELECT value FROM data WHERE key = :key;';
        $statement = $this->connection->prepare($sql);
        $statement->execute([ 'key' => $key ]);

        if($row = $statement->fetch())
        {
            return $row['value'];
        }
        else
        {
            return $default;
        }
    }

    public function save(string $key, string $value)
    {
        if($this->exists($key))
        {
            $this->update($key, $value);
        }
        else
        {
            $this->insert($key, $value);
        }
    }

    private function insert(string $key, string $value)
    {
        $sql = 'INSERT INTO data(key, value) VALUES(:key, :value);';
        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'key' => $key,
            'value' => $value
        ]);
    }

    private function update(string $key, string $value)
    {
        $sql = 'UPDATE data SET value = :value WHERE key = :key;';
        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'key' => $key,
            'value' => $value
        ]);
    }

    private function exists(string $key): bool
    {
        $sql = 'SELECT 1 FROM data WHERE key = :key;';
        $statement = $this->connection->prepare($sql);
        $statement->execute([ 'key' => $key ]);
        return $statement->fetch() !== false;
    }
}