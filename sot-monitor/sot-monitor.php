#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Ontic\Sot\Monitor\Plugin\PluginInterface;
use Ontic\Sot\Monitor\Service\Factory\ContainerFactory;
use Ontic\Sot\Monitor\Service\PluginScheduler;

$container = ContainerFactory::get(__DIR__);

if($idx = array_search('--plugin', $argv))
{
    $className = $argv[$idx+1];
    /** @var PluginInterface $plugin */
    $plugin = $container->get($className);
    $plugin->run();
    exit();
}

/** @var PluginScheduler $scheduler */
$scheduler = $container->get(PluginScheduler::class);
$scheduler->execute();

