<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Timestamp.
 */

namespace CultuurNet\UDB3;

/**
 * Provices a class for a timestamp.
 */
class Timestamp
{

    /**
     * @var string
     */
    protected $startDate;

    /**
     * @var string
     */
    protected $endDate;

    /**
     * Constructor
     *
     * @param string $startDate
     * @param string $endDate
     */
    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }
}
