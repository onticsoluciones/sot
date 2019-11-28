<?php

namespace Ontic\Sot\Monitor\Event;

use Ontic\Sot\Monitor\Model\Alert;
use Symfony\Contracts\EventDispatcher\Event;

class AlertEvent extends Event
{
    public const NAME = 'alert';

    /** @var Alert */
    private $alert;

    /**
     * @param Alert $alert
     */
    public function __construct(Alert $alert)
    {
        $this->alert = $alert;
    }

    /**
     * @return Alert
     */
    public function getAlert(): Alert
    {
        return $this->alert;
    }
}