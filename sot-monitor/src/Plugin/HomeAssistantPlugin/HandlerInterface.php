<?php

namespace Ontic\Sot\Monitor\Plugin\HomeAssistantPlugin;

interface HandlerInterface
{
    function handle(string $message);
}