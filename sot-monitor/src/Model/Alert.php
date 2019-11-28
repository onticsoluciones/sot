<?php

namespace Ontic\Sot\Monitor\Model;

class Alert
{
    const PRIORITY_LOW = 0;
    const PRIORITY_NORMAL = 1;
    const PRIORITY_HIGH = 2;
    const PRIORITY_CRITICAL = 3;

    /** @var string */
    private $type;
    /** @var array */
    private $data;
    /** @var int */
    private $timestamp;
    /** @var int */
    private $priority;

    public function __construct(string $type, array $data, int $timestamp, int $priority)
    {
        $this->type = $type;
        $this->data = $data;
        $this->timestamp = $timestamp;
        $this->priority = $priority;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}