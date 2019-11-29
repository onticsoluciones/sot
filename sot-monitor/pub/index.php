<?php

set_time_limit(600);

require_once __DIR__ . '/../vendor/autoload.php';

use Ontic\Sot\Monitor\Repository\AlertRepository;
use Ontic\Sot\Monitor\Service\Factory\ContainerFactory;

$container = ContainerFactory::get(__DIR__ . '/../');
$repo = $container->get(AlertRepository::class);
$response = [];
$alerts = getAlerts($repo, @$_GET['from']);
while(count($alerts) === 0)
{
    sleep(0.1);
    $alerts = getAlerts($repo, @$_GET['from']);
}

foreach ($alerts as $alert)
{
    $response[] = [
        'type' => $alert->getType(),
        'data' => $alert->getData(),
        'timestamp' => $alert->getTimestamp(),
        'priority' => $alert->getPriority()
    ];
}

header('Content-Type: application/json');
echo json_encode($response);

function getAlerts(AlertRepository $repo, $timestamp)
{
    if($timestamp)
    {
        $alerts = $repo->findAllAfter($timestamp);
    }
    else
    {
        $alerts = $repo->findRecent(10);
    }

    return $alerts;
}
