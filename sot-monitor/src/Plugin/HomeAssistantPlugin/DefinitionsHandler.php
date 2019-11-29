<?php

namespace Ontic\Sot\Monitor\Plugin\HomeAssistantPlugin;

use Ontic\Sot\Monitor\Event\AlertEvent;
use Ontic\Sot\Monitor\Model\Alert;
use Ontic\Sot\Monitor\Model\Definition;
use Ontic\Sot\Monitor\Repository\DefinitionRepository;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DefinitionsHandler implements HandlerInterface
{
    /** @var DefinitionRepository */
    private $repository;
    /** @var EventDispatcher */
    private $eventDispatcher;
    /** @var string */
    private $previousMessage = '';

    public function __construct(DefinitionRepository $repository, EventDispatcher $eventDispatcher)
    {
        $this->repository = $repository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(string $message)
    {
        foreach($this->repository->findAllBySource('hasslog') as $definition)
        {
            if(preg_match($definition->getPattern(), $message))
            {
                $this->dispatchEvent($definition);
            }
            elseif(preg_match($definition->getPattern(), $this->previousMessage . "\n" . $message))
            {
                $this->dispatchEvent($definition);
            }
        }

        $this->previousMessage = $message;
    }

    private function dispatchEvent(Definition $definition)
    {
        $alert = new Alert($definition->getAlertType(), [
            'device' => $definition->getName()
        ], time(), $definition->getPriority());

        $this->eventDispatcher->dispatch(new AlertEvent($alert), AlertEvent::NAME);
    }
}