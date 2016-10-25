<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\CalendarType;

class AvailableTo
{
    /**
     * @var \DateTimeInterface
     */
    private $availableTo;

    /**
     * AvailableTo constructor.
     * @param \DateTimeInterface $availableTo
     */
    private function __construct(\DateTimeInterface $availableTo) {
        $this->availableTo = $availableTo;
    }

    /**
     * @param CalendarInterface $calendar
     * @return AvailableTo
     */
    static public function createFromCalendar(CalendarInterface $calendar)
    {
        if ($calendar->getType() === CalendarType::PERMANENT()) {
            $availableTo = new \DateTime('2100-01-01T00:00:00Z');
        } else if ($calendar->getType() === CalendarType::SINGLE()) {
            $availableTo = $calendar->getStartDate();
        } else {
            $availableTo = $calendar->getEndDate();
        }

        return new self($availableTo);
    }

    /**
     * @return \DateTimeInterface
     */
    public function getAvailableTo()
    {
        return $this->availableTo;
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->availableTo->format(\DateTime::ATOM);
    }
}
