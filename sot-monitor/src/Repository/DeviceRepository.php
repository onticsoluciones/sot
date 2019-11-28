<?php

namespace Ontic\Sot\Monitor\Repository;

use Ontic\Sot\Monitor\Model\Device;
use PDO;

class DeviceRepository
{
    /** @var PDO */
    private $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(Device $device)
    {
        $sql = 'INSERT INTO device(entity_id, name, acknowledged) VALUES (:entity_id, :name, :acknowledged);';
        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'entity_id' => $device->getEntityId(),
            'name' => $device->getName(),
            'acknowledged' => $device->isAcknowledged() ? 1 : 0
        ]);
    }

    public function getByEntityId($entityId): ?Device
    {
        $sql = 'SELECT * FROM device WHERE entity_id = :entity_id';
        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'entity_id' => $entityId
        ]);

        if($row = $statement->fetch())
        {
            return $this->parse($row);
        }

        return null;
    }

    private function parse(array $row): Device
    {
        $id = $row['id'];
        $deviceId = $row['entity_id'];
        $name = $row['name'];
        $acknowledged = $row['acknowledged'] == 1;

        return new Device($id, $deviceId, $name, $acknowledged);
    }
}