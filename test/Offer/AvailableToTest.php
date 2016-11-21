<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\CalendarType;

class AvailableToTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider calendarsDateProvider
     * @param CalendarInterface $calendar
     * @param \DateTimeInterface $expectedAvailableTo
     */
    public function it_creates_available_to_from_calendars(
        CalendarInterface $calendar,
        \DateTimeInterface $expectedAvailableTo
    ) {
        $availableTo = AvailableTo::createFromCalendar($calendar);

        $this->assertEquals(
            $expectedAvailableTo,
            $availableTo->getAvailableTo()
        );
    }

    /**
     * @return array
     */
    public function calendarsDateProvider()
    {
        $startDate = new \DateTime('2016-10-10T18:19:20');
        $endDate = new \DateTime('2016-10-18T20:19:18');

        return [
            [
                new Calendar(CalendarType::PERMANENT()),
                new \DateTime('2100-01-01T00:00:00Z')
            ],
            [
                new Calendar(CalendarType::SINGLE(), $startDate, $endDate),
                $endDate
            ],
            [
                new Calendar(CalendarType::PERIODIC(), $startDate, $endDate),
                $endDate
            ],
            [
                new Calendar(CalendarType::MULTIPLE(), $startDate, $endDate),
                $endDate
            ],
        ];
    }
}
