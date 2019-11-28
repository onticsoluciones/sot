<?php

namespace Ontic\Sot\Monitor\Model;

class Device
{
    /** @var int */
    private $id;
    /** @var string */
    private $entityId;
    /** @var string */
    private $name;
    /** @var bool */
    private $acknowledged;

    public function __construct(int $id, string $entityId, string $name, bool $acknowledged)
    {
        $this->id = $id;
        $this->entityId = $entityId;
        $this->name = $name;
        $this->acknowledged = $acknowledged;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEntityId(): string
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isAcknowledged(): bool
    {
        return $this->acknowledged;
    }
}