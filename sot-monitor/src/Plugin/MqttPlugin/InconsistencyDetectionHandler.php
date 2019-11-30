<?php

namespace Ontic\Sot\Monitor\Plugin\MqttPlugin;

use Ontic\Sot\Monitor\Repository\DataRepository;

class InconsistencyDetectionHandler implements HandlerInterface
{
    /** @var DataRepository */
    private $dataRepository;

    public function __construct(DataRepository $dataRepository)
    {
        $this->dataRepository = $dataRepository;
    }

    function handle(string $topic, string $payload)
    {
        if($topic !== 'zigbee2mqtt/0x90fd9ffffef4dca6/set')
        {
            return;
        }

        $payload = json_decode($payload, true);
        if($payload['state'] === 'OFF')
        {
            $this->dataRepository->save('last_set_off', time());
        }
    }
}