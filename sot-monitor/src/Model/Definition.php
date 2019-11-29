<?php

namespace Ontic\Sot\Monitor\Model;

class Definition
{
    /** @var string */
    private $name;
    /** @var string */
    private $source;
    /** @var string */
    private $pattern;
    /** @var string */
    private $alertType;
    /** @var string */
    private $priority;

    public function __construct(string $name, string $source, string $pattern, string $alertType, string $priority)
    {
        $this->name = $name;
        $this->source = $source;
        $this->pattern = $pattern;
        $this->alertType = $alertType;
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @return string
     */
    public function getAlertType(): string
    {
        return $this->alertType;
    }

    /**
     * @return string
     */
    public function getPriority(): string
    {
        return $this->priority;
    }
}