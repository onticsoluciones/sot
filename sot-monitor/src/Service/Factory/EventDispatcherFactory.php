<?php

namespace Ontic\Sot\Monitor\Service\Factory;

use Ontic\Sot\Monitor\Subscriber\AlertSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventDispatcherFactory
{
    /** @var AlertSubscriber */
    private $alertSubscriber;

    public function __construct(AlertSubscriber $alertSubscriber)
    {
        $this->alertSubscriber = $alertSubscriber;
    }

    public function get(): EventDispatcher
    {
        $event = new EventDispatcher();
        $event->addSubscriber($this->alertSubscriber);
        return $event;
    }
}