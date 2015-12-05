<?php

namespace CultuurNet\UDB3;

/**
 * Interface for calendars.
 */
interface CalendarInterface
{
    /**
     * Get current calendar type.
     *
     * @return string
     */
    public function getType();

    /**
     * Get the start date.
     *
     * @return string
     */
    public function getStartDate();

    /**
     * Get the end date.
     *
     * @return string
     */
    public function getEndDate();

    /**
     * Get the opening hours.
     *
     * @return array
     */
    public function getOpeningHours();

    /**
     * Get timestamps.
     *
     * @return Timestamp[]
     */
    public function getTimestamps();
}
