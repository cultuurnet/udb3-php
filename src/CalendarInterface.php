<?php

/**
 * @file
 * Contains CultuurNet\UDB3\CalendarBase.
 */

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;

/**
 * Abstract base class for calendars.
 */
interface CalendarInterface extends SerializableInterface
{

    public function getType();

    public function getStartDate();

    public function getEndDate();

    public function getOpeningHours();

    public function getTimestamps();
}
