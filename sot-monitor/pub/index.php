<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Ontic\Sot\Monitor\Repository\AlertRepository;
use Ontic\Sot\Monitor\Service\Factory\ContainerFactory;

$container = ContainerFactory::get(__DIR__ . '/../');
/** @var AlertRepository $repo */
$repo = $container->get(AlertRepository::class);
$alerts = [];
foreach($repo->findRecent(10) as $device)
{
    $alerts[] = [
        'type' => $device->getType(),
        'data' => $device->getData(),
        'timestamp' => $device->getTimestamp(),
        'priority' => $device->getPriority()
    ];
}

header('Content-Type: application/json');
echo json_encode($alerts);
