<?php

namespace Ontic\Sot\Monitor\Plugin;

use Ontic\Sot\Monitor\Plugin\MqttPlugin\HandlerInterface;
use Ontic\Sot\Monitor\Plugin\MqttPlugin\IntermittentPowerHandler;

class MqttPlugin implements PluginInterface
{
    /** @var HandlerInterface[] */
    private $handlers;

    public function __construct
    (
        IntermittentPowerHandler $intermittentPowerHandler
    )
    {
        $this->handlers[] = $intermittentPowerHandler;
    }

    public function run()
    {
        $c = new \Mosquitto\Client("PHP");
        $c->onMessage(function($message) {
            $this->onMessage($message);
        });
        $c->connect("10.30.37.53");
        $c->subscribe('#', 1);
        $c->loopForever();
    }

    public function onMessage($message)
    {
        foreach($this->handlers as $handler)
        {
            $handler->handle($message->topic, $message->payload);
        }
    }
}