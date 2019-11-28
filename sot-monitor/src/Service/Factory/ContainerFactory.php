<?php

namespace Ontic\Sot\Monitor\Service\Factory;

use Exception;
use Ontic\Sot\Monitor\Model\Environment;
use Psr\Container\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContainerFactory
{
    /**
     * @param string $rootDir
     * @return ContainerInterface
     * @throws Exception
     */
    public static function get(string $rootDir): ContainerInterface
    {
        global $argv;
        $container = new ContainerBuilder();


        $container->setParameter('root_dir', $rootDir);
        $loader = new YamlFileLoader($container, new FileLocator($rootDir));
        $loader->load('services.yml');

        $environment = new Environment($rootDir, PHP_BINARY, $argv[0]);
        $container->set(Environment::class, $environment);
        $container->setDefinition(Environment::class, (new Definition())->setSynthetic(true));

        $container->compile();

        return $container;
    }
}