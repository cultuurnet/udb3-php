<?php


namespace CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\Promotion;

use CultureFeed_Uitpas_Event_CultureEvent;
use CultureFeed_Uitpas_Calendar;
use CultureFeed_Uitpas_Calendar_Timestamp;

class EventOrganizerPromotionQueryFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var EventOrganizerPromotionQueryFactory
     */
    protected $queryFactory;

    public function setUp()
    {
        $this->queryFactory = new EventOrganizerPromotionQueryFactory();
    }

    /**
     * @test
     */
    public function it_creates_query_options_with_cashing_period_that_matches_event()
    {
        $eventCalendar = new CultureFeed_Uitpas_Calendar();
        $today = new \DateTimeImmutable();
        $tomorrow = $today->modify('+1 day');

        $timestampToday = new CultureFeed_Uitpas_Calendar_Timestamp();
        $timestampToday->date = $today->getTimestamp();

        $timestampTomorrow = new CultureFeed_Uitpas_Calendar_Timestamp();
        $timestampTomorrow->date = $tomorrow->getTimestamp();

        $eventCalendar->addTimestamp($timestampToday);
        $eventCalendar->addTimestamp($timestampTomorrow);

        $event = new CultureFeed_Uitpas_Event_CultureEvent();
        $event->calendar = $eventCalendar;

        $queryOptions = $this->queryFactory->createForEvent($event);

        $expectedFromDate = $today->setTime(0, 0, 0)->getTimestamp();
        $expectedToDate = $tomorrow->setTime(24, 59, 59)->getTimestamp();

        $this->assertEquals($queryOptions->cashingPeriodBegin, $expectedFromDate);
        $this->assertEquals($queryOptions->cashingPeriodEnd, $expectedToDate);
    }
}
