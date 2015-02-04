<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Command;


use CultuurNet\UDB3\EventExport\EventExportQuery;
use ValueObjects\Web\EmailAddress;

class ExportEventsAsCSV
{
    /**
     * @var EventExportQuery
     */
    private $query;

    /**
     * @var null|EmailAddress
     */
    private $address;

    /**
     * @param EventExportQuery $query
     * @param EmailAddress|null $address
     */
    public function __construct(EventExportQuery $query, EmailAddress $address = null)
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
     * @return null|EmailAddress
     */
    public function getAddress()
    {
        return $this->address;
    }
}
