<?php

namespace Ontic\Sot\Monitor\Subscriber;

use Ontic\Sot\Monitor\Event\AlertEvent;
use Ontic\Sot\Monitor\Repository\AlertRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AlertSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            AlertEvent::NAME => 'onAlert'
        ];
    }

    /** @var AlertRepository */
    private $alertRepository;

    public function __construct(AlertRepository $alertRepository)
    {
        $this->alertRepository = $alertRepository;
    }

    public function onAlert(AlertEvent $alertEvent)
    {
        echo sprintf('%s %s', $alertEvent->getAlert()->getType(), $alertEvent->getAlert()->getData()['device']) . PHP_EOL;
        $this->alertRepository->save($alertEvent->getAlert());
    }
}