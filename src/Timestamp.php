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
    protected $date;

    /**
     * @var string
     */
    protected $timestart;

    /**
     * @var string
     */
    protected $timeend;

    public function __construct($date, $timestart, $timeend)
    {

        CalendarUtility::validateDate($date);
        $this->date = $date;

        CalendarUtility::validateTime($timestart);
        $this->timestart = $timestart;

        CalendarUtility::validateTime($timeend);
        $this->timeend = $timeend;

    }

    function getDate()
    {
        return $this->date;
    }

    function getTimestart()
    {
        return $this->timestart;
    }

    function getTimeend()
    {
        return $this->timeend;
    }

}
