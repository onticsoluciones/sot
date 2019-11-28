<?php

namespace Ontic\Sot\Monitor\Model;

class Alert
{
    /** @var string */
    private $type;
    /** @var array */
    private $data;
    /** @var int */
    private $timestamp;

    public function __construct(string $type, array $data, int $timestamp)
    {
        $this->type = $type;
        $this->data = $data;
        $this->timestamp = $timestamp;
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
}