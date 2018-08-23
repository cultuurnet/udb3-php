<?php

namespace CultuurNet\UDB3\MyOrganizers\ReadModel\Doctrine;

trait DBALHelperTrait
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    private function parameter(string $name): string
    {
        return ':' . $name;
    }
}
