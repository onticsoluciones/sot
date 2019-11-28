<?php

namespace Ontic\Sot\Monitor\Service\Factory;

use Ontic\Sot\Monitor\Model\Configuration;
use Ontic\Sot\Monitor\Model\Environment;
use Symfony\Component\Yaml\Yaml;

class ConfigurationFactory
{
    /** @var Environment */
    private $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    public function get()
    {
        $file = $this->environment->getRootDir() . '/parameters.yml';
        $data = Yaml::parseFile($file);
        return new Configuration($data);
    }
}