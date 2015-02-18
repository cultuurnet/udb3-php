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

        $this->setDate($date);
        $this->setTimestart($timestart);
        $this->setTimeend($timeend);
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

    function setDate($date)
    {

        CalendarBase::validateDate($date);

        $this->date = $date;
    }

    function setTimestart($timestart)
    {

        CalendarBase::validateTime($timestart);
        $this->timestart = $timestart;
    }

    function setTimeend($timeend)
    {

        CalendarBase::validateTime($timeend);
        $this->timeend = $timeend;
    }

}
