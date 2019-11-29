<?php

namespace Ontic\Sot\Monitor\Repository;

use Ontic\Sot\Monitor\Model\Definition;
use Ontic\Sot\Monitor\Model\Environment;

class DefinitionRepository
{
    /** @var Environment */
    private $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @param string $source
     * @return Definition[]
     */
    public function findAllBySource(string $source): array
    {
        return array_filter($this->findAll(), function(Definition $definition) use($source) {
            return $definition->getSource() === $source;
        });
    }

    /**
     * @return Definition[]
     */
    public function findAll(): array
    {
        $definitionsFile = sprintf('%s/data/definitions.json', $this->environment->getRootDir());
        $definitions = json_decode(file_get_contents($definitionsFile), true);

        return array_map(function($definitionData) {
            return $this->parseDefinition($definitionData);
        }, $definitions);
    }

    private function parseDefinition(array $data): Definition
    {
        $name = $data['name'];
        $source = $data['source'];
        $pattern = $data['pattern'];
        $alertType = $data['alert_type'];
        $priority = $data['priority'];

        return new Definition($name, $source, $pattern, $alertType, $priority);
    }
}