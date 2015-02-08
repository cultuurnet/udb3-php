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
     * @var string[]
     */
    private $selection;

    /**
     * @var string[]
     */
    private $include;

    /**
     * @param EventExportQuery $query
     * @param EmailAddress|null $address
     * @param string[] $selection
     * @param string[] $include
     */
    public function __construct(EventExportQuery $query, EmailAddress $address = null,
      $selection = null, $include = null)
    {
        if ($query->isEmpty()) {
            throw new \RuntimeException('Query can not be empty');
        }

        $this->query = $query;
        $this->address = $address;
        $this->selection = $selection;
        $this->include = $include;
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

    /**
     * @return null|\string[]
     */
    public function getSelection()
    {
        return $this->selection;
    }

    /**
     * @return null|\string[]
     */
    public function getInclude()
    {
        return $this->include;
    }
}
