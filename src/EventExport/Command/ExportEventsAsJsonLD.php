<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Command;


use CultuurNet\UDB3\EventExport\EventExportQuery;
use CultuurNet\UDB3\TrimmedString;

class ExportEventsAsJsonLD
{
    /**
     * @var EventExportQuery
     */
    private $query;

    /**
     * @param TrimmedString $query
     */
    public function __construct(EventExportQuery $query)
    {
        if ($query->isEmpty()) {
            throw new \RuntimeException('Query can not be empty');
        }

        $this->query = $query;
    }

    /**
     * @return EventExportQuery The query.
     */
    public function getQuery()
    {
        return $this->query;
    }
}
