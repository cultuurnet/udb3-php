<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Command;


use CultuurNet\UDB3\EventExport\EventExportQuery;

class ExportEventsAsCSV
{
    /**
     * @var EventExportQuery
     */
    private $query;

    /**
     * @var null|string
     */
    private $address;

    /**
     * @param EventExportQuery $query
     * @param string|null $address
     */
    public function __construct(EventExportQuery $query, $address = null)
    {
        if ($query->isEmpty()) {
            throw new \RuntimeException('Query can not be empty');
        }

        $this->query = $query;
        $this->address = $address;
    }

    /**
     * @return EventExportQuery The query.
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return null|string
     */
    public function getAddress()
    {
        return $this->address;
    }
}
