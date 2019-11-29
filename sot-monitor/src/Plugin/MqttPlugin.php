<?php

namespace Ontic\Sot\Monitor\Plugin;

use Ontic\Sot\Monitor\Model\Configuration;
use Ontic\Sot\Monitor\Plugin\MqttPlugin\HandlerInterface;
use Ontic\Sot\Monitor\Plugin\MqttPlugin\IntermittentPowerHandler;

class MqttPlugin implements PluginInterface
{
    /** @var Configuration */
    private $config;
    /** @var HandlerInterface[] */
    private $handlers;

    public function __construct
    (
        Configuration $config,
        IntermittentPowerHandler $intermittentPowerHandler
    )
    {
        $this->config = $config;
        $this->handlers[] = $intermittentPowerHandler;
    }

    public function run()
    {
        $c = new \Mosquitto\Client("PHP");
        $c->onMessage(function($message) {
            $this->onMessage($message);
        });
        $c->connect($this->config['mqtt']['host']);
        $c->subscribe('#', 1);
        $c->loopForever();
    }

    public function onMessage($message)
    {
        var_dump($message);
        foreach($this->handlers as $handler)
        {
            $handler->handle($message->topic, $message->payload);
        }
    }
}