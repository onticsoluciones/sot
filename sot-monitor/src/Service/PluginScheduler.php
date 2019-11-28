<?php

namespace Ontic\Sot\Monitor\Service;

use Ontic\Sot\Monitor\Model\Environment;

class PluginScheduler
{
    /** @var Environment */
    private $environment;
    /** @var PluginFinder */
    private $pluginFinder;

    public function __construct(Environment $environment, PluginFinder $pluginFinder)
    {
        $this->environment = $environment;
        $this->pluginFinder = $pluginFinder;
    }

    public function execute()
    {
        $pluginClasses = $this->pluginFinder->find();
        $commands = $this->getCommands($pluginClasses);
        $childPids = [];

        for ($i = 0; $i < count($commands); $i++)
        {
            $pid = pcntl_fork();
            switch ($pid)
            {
                case -1:
                    die('could not fork');
                case 0:
                    system($commands[$i]);
                    exit();
                default:
                    $childPids[] = $pid;
            }
        }

        while (!empty($childPids))
        {
            foreach ($childPids as $key => $pid)
            {
                $status = null;
                $res = pcntl_waitpid($pid, $status, WNOHANG);

                if ($res == -1 || $res > 0)
                {
                    unset($childPids[$key]);
                }
            }

            sleep(1);
        }
    }

    /**
     * @param string[] $pluginClasses
     * @return string[]
     */
    private function getCommands(array $pluginClasses): array
    {
        return array_map(function ($className)
        {
            return sprintf('%s %s --plugin "%s"',
                $this->environment->getPhpPath(),
                $this->environment->getScriptPath(),
                $className
            );
        }, $pluginClasses);
    }
}