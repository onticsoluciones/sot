<?php

namespace Ontic\Sot\Monitor\Plugin\MqttPlugin;

interface HandlerInterface
{
    function handle(string $topic, string $payload);
}