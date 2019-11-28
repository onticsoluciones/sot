<?php

namespace Ontic\Sot\Monitor\Plugin\MqttPlugin;

use Ontic\Sot\Monitor\Event\AlertEvent;
use Ontic\Sot\Monitor\Model\Alert;
use Symfony\Component\EventDispatcher\EventDispatcher;

class IntermittentPowerHandler implements HandlerInterface
{
    /** @var EventDispatcher */
    private $eventDispatcher;
    /** @var string */
    private $rg = '/^([^\/]+)\/cmnd\/POWER/';
    /** @var array */
    private $events = [];

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    function handle(string $topic, string $payload)
    {
        if(!preg_match($this->rg, $topic, $matches) || !in_array($payload, [ 'ON', 'OFF' ]))
        {
            return;
        }

        $timestamp = time();
        $key = $matches[1];
        $value = $payload;

        $this->events[$key][] = [
            'timestamp' => $timestamp,
            'value' => $value
        ];

        $consecutiveEvents = 0;
        for($i=1; $i<count($this->events[$key]); $i++)
        {
            $previousEvent = $this->events[$key][$i - 1];
            $currentEvent = $this->events[$key][$i];

            if
            (
                ($currentEvent['value'] !== $previousEvent['value']) &&
                ($currentEvent['timestamp'] - $previousEvent['timestamp'] < 3)
            )
            {
                $consecutiveEvents++;
            }
            else
            {
                $consecutiveEvents = 0;
            }
        }

        if($consecutiveEvents > 3)
        {
            $alert = new Alert('intermittent_power', [ 'device' => $key ], time(), Alert::PRIORITY_HIGH);
            $this->eventDispatcher->dispatch(new AlertEvent($alert), AlertEvent::NAME);
        }
    }
}