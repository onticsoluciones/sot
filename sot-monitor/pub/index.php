<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Ontic\Sot\Monitor\Repository\DeviceRepository;
use Ontic\Sot\Monitor\Service\Factory\ContainerFactory;

$container = ContainerFactory::get(__DIR__ . '/../');
/** @var DeviceRepository $repo */
$repo = $container->get(DeviceRepository::class);
$alerts = [];
foreach($repo->findAllUnacknowledged() as $device)
{
    $alerts[] = [
        'type' => 'new_device',
        'data' => [
            'entity_id' => $device->getEntityId(),
            'name' => $device->getName()
        ]
    ];
}

header('Content-Type: application/json');
echo json_encode($alerts);
