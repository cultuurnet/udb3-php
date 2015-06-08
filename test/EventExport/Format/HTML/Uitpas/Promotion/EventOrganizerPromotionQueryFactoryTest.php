<?php


namespace CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\Promotion;

use CultureFeed_Uitpas_Calendar;
use CultureFeed_Uitpas_Calendar_Timestamp;
use CultureFeed_Uitpas_Event_CultureEvent;
use CultureFeed_Uitpas_Passholder_Query_SearchPromotionPointsOptions;

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
        $event->organiserId = 'xyz';
        $event->calendar = $eventCalendar;

        $query = $this->queryFactory->createForEvent($event);

        $expectedFromDate = $today->setTime(0, 0, 0)->getTimestamp();
        $expectedToDate = $tomorrow->setTime(24, 59, 59)->getTimestamp();

        $expectedQuery = $this->createBaseQuery();
        $expectedQuery->balieConsumerKey = $event->organiserId;
        $expectedQuery->cashingPeriodBegin = $expectedFromDate;
        $expectedQuery->cashingPeriodEnd = $expectedToDate;

        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * Creates the base for the query, with all necessary properties set that
     * are independent from the cultural event passed to createForEvent().
     *
     * @return CultureFeed_Uitpas_Passholder_Query_SearchPromotionPointsOptions
     */
    private function createBaseQuery()
    {
        $expectedQueryOptions = new CultureFeed_Uitpas_Passholder_Query_SearchPromotionPointsOptions();
        $expectedQueryOptions->max = 2;
        $expectedQueryOptions->unexpired = true;

        return $expectedQueryOptions;
    }
}
