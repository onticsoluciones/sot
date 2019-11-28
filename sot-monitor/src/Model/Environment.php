<?php

namespace Ontic\Sot\Monitor\Model;

class Environment
{
    /** @var string */
    private $rootDir;
    /** @var string */
    private $phpPath;
    /** @var string|null */
    private $scriptPath;

    public function __construct(string $rootDir, string $phpPath, ?string $scriptPath)
    {
        $this->rootDir = $rootDir;
        $this->phpPath = $phpPath;
        $this->scriptPath = $scriptPath;
    }

    /**
     * @return string
     */
    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    /**
     * @return string
     */
    public function getPhpPath(): string
    {
        return $this->phpPath;
    }

    /**
     * @return string|null
     */
    public function getScriptPath(): ?string
    {
        return $this->scriptPath;
    }
}