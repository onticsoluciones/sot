<?php

namespace Ontic\Sot\Monitor\Service;

class PluginFinder
{
    public function find(): array
    {
        $classNames = [];
        $pluginDir = __DIR__ . '/../Plugin';

        foreach(new \DirectoryIterator($pluginDir) as $entry)
        {
            $filename = $entry->getFilename();
            $length = strlen('Plugin.php');
            if(substr($filename, -$length) === 'Plugin.php')
            {
                $class = "Ontic\\Sot\\Monitor\\Plugin\\" . str_replace('.php', '', $filename);
                $classNames[]= $class;

            }
        }
        return $classNames;
    }

}