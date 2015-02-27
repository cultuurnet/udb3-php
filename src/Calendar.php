<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Calendar.
 */

namespace CultuurNet\UDB3;

/**
 * a Calendar for events and places.
 */
class Calendar
{
    
    protected $startDate = NULL;
    protected $endDate = NULL;

    public function __construct()
    {
        parent::__construct();
    }
    
    public function setStartDate($startDate) {
        $this->startDate = $startDate;
    }
    
    public function setEndDate($endDate) {
        $this->endDate = $endDate;
    }

}
