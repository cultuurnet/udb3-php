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
    protected $startHour;

    /**
     * @var boolean
     */
    protected $showStartHour;

    /**
     * @var string
     */
    protected $endHour;

    /**
     * @var boolean
     */
    protected $showEndHour;

    /**
     * Constructor
     *
     * @param type $date
     * @param type $startHour
     * @param type $endHour
     * @param type $showStartHour
     * @param type $showEndHour
     */
    public function __construct($date, $startHour, $endHour, $showStartHour = true, $showEndHour = true)
    {

        //CalendarUtility::validateDate($date);
        $this->date = $date;

        //CalendarUtility::validateTime($startHour);
        $this->timestart = $startHour;

        //CalendarUtility::validateTime($endHour);
        $this->timeend = $endHour;

        $this->showStartHour = $showStartHour;
        $this->showEndHour = $showEndHour;

    }

    public function getDate()
    {
        return $this->date;
    }

    public function getTimestart()
    {
        return $this->timestart;
    }

    public function getTimeend()
    {
        return $this->timeend;
    }

    public function showStartHour()
    {
        return $this->showStartHour;
    }

    public function showEndHour()
    {
        return $this->showEndHour;
    }
}
