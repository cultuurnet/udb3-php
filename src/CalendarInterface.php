<?php

namespace CultuurNet\UDB3;
use DateTimeInterface;

/**
 * Interface for calendars.
 */
interface CalendarInterface
{
    /**
     * Get current calendar type.
     *
     * @return CalendarType
     */
    public function getType();

    /**
     * Get the start date.
     *
     * @return DateTimeInterface
     */
    public function getStartDate();

    /**
     * Get the end date.
     *
     * @return DateTimeInterface
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
