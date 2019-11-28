<?php

namespace Ontic\Sot\Monitor\Service\Factory;

use Ontic\Sot\Monitor\Model\Environment;
use PDO;

class ConnectionFactory
{
    /** @var Environment */
    private $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    public function get(): PDO
    {
        $connection = new PDO(sprintf('sqlite:%s/data/database.sqlite3', $this->environment->getRootDir()));
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $connection;
    }
}