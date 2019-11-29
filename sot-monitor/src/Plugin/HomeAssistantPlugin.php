<?php

namespace Ontic\Sot\Monitor\Plugin;

use Ontic\Sot\Monitor\Event\AlertEvent;
use Ontic\Sot\Monitor\Model\Alert;
use Ontic\Sot\Monitor\Model\Configuration;
use Ontic\Sot\Monitor\Model\Device;
use Ontic\Sot\Monitor\Plugin\HomeAssistantPlugin\DefinitionsHandler;
use Ontic\Sot\Monitor\Plugin\HomeAssistantPlugin\HandlerInterface;
use Ontic\Sot\Monitor\Repository\DeviceRepository;
use Symfony\Component\EventDispatcher\EventDispatcher;

class HomeAssistantPlugin implements PluginInterface
{
    /** @var Configuration */
    private $config;
    /** @var EventDispatcher */
    private $eventDispatcher;
    /** @var DeviceRepository */
    private $deviceRepository;
    /** @var HandlerInterface[] */
    private $handlers;

    /** @var array  */
    private $knownDevices = [];
    /** @var string */
    private $buffer;

    public function __construct
    (
        Configuration $config,
        EventDispatcher $eventDispatcher,
        DeviceRepository $deviceRepository,
        DefinitionsHandler $definitionsHandler
    )
    {
        $this->config = $config;
        $this->eventDispatcher = $eventDispatcher;
        $this->deviceRepository = $deviceRepository;
        $this->handlers[] = $definitionsHandler;
    }

    function run()
    {
        $host = $this->config['homeassistant']['host'];
        $port = $this->config['homeassistant']['port'];

        $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
        socket_set_block($socket);
        $result = socket_connect($socket, $host, $port) or die("Could not bind to socket\n");
        while(($result = socket_read($socket, 1024)) !== false)
        {
            $this->buffer .= $result;
            $lines = [];
            while(($idx = strpos($this->buffer, "\n",)) !== false)
            {
                $fragments = preg_split("/\n/", $this->buffer, 2);
                $lines[] = $fragments[0];
                $this->buffer = $fragments[1];
            }

            foreach($lines as $line)
            {
                $this->findDevice($line);
                foreach($this->handlers as $handler)
                {
                    $handler->handle($line);
                }
            }
        }
    }

    private function findDevice(string $line)
    {
        if(!preg_match('/entity_id=([^,]+).+?friendly_name=([^,]+)/m', $line, $matches))
        {
            return;
        }

        $entityId = $matches[1];
        $name = $matches[2];

        if(($idx = strpos($name, '@')) !== false)
        {
            $name = substr($name, 0, $idx - 1);
        }

        if(isset($this->knownDevices[$entityId]))
        {
            return;
        }

        $this->saveDevice($entityId, $name);
    }

    /**
     * @param $entityId
     * @param $name
     */
    private function saveDevice($entityId, $name)
    {
        $this->knownDevices[$entityId] = 1;

        if($this->deviceRepository->getByEntityId($entityId))
        {
            return;
        }

        $this->deviceRepository->save(new Device(0, $entityId, $name, false));

        $alert = new Alert('new_device', [
            'entity_id' => $entityId,
            'name' => $name
        ], time(), Alert::PRIORITY_NORMAL);

        $this->eventDispatcher->dispatch(new AlertEvent($alert), AlertEvent::NAME);
    }
}