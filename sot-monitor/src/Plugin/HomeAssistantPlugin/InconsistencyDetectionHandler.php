<?php

namespace Ontic\Sot\Monitor\Plugin\HomeAssistantPlugin;

use Ontic\Sot\Monitor\Event\AlertEvent;
use Ontic\Sot\Monitor\Model\Alert;
use Ontic\Sot\Monitor\Repository\DataRepository;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InconsistencyDetectionHandler implements HandlerInterface
{
    private $re = '/zigbee2mqtt\/0x90fd9ffffef4dca6: b\'{"state":"OFF"}\'/m';

    /** @var DataRepository */
    private $dataRepository;
    /** @var EventDispatcher */
    private $eventDistpacher;

    public function __construct(DataRepository $dataRepository, EventDispatcher $eventDispatcher)
    {
        $this->dataRepository = $dataRepository;
        $this->eventDistpacher = $eventDispatcher;
    }

    function handle(string $message)
    {
        if(!preg_match($this->re, $message))
        {
            return;
        }

        $mqttTimestamp = (int) $this->dataRepository->get('last_set_off', 0);
        $now = time();

        if($now - $mqttTimestamp > 5)
        {
            $alert = new Alert(Alert::TYPE_INCONSISTENCY, [
                'device' => 'IKEA Lightbulb'
            ], time(), Alert::PRIORITY_CRITICAL);

            $this->eventDistpacher->dispatch(new AlertEvent($alert), AlertEvent::NAME);
        }
    }
}