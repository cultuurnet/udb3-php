<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\EventInfo;

use CultureFeed_Uitpas;
use CultureFeed_Uitpas_CardSystem;
use CultureFeed_Uitpas_DistributionKey;
use CultureFeed_Uitpas_Event_CultureEvent;
use CultureFeed_Uitpas_Event_Query_SearchEventsOptions;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\DistributionKey\DistributionKeySpecification;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\DistributionKey\KansentariefDiscountSpecification;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\DistributionKey\KansentariefForCurrentCardSystemSpecification;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\DistributionKey\KansentariefForOtherCardSystemsSpecification;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\Event\EventAdvantage;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\Event\PointCollectingSpecification;

class CultureFeedEventInfoService implements EventInfoServiceInterface
{
    /**
     * @var CultureFeed_Uitpas
     */
    protected $uitpas;

    /**
     * @var DistributionKeySpecification
     */
    protected $kansenTariefForCurrentCardSystem;

    /**
     * @var DistributionKeySpecification
     */
    protected $kansenTariefForOtherCardSystems;

    /**
     * @var KansentariefDiscountSpecification
     */
    protected $kansentariefDiscount;

    /**
     * @var PointCollectingSpecification
     */
    protected $pointCollecting;

    /**
     * @param CultureFeed_Uitpas $uitpas
     */
    public function __construct(CultureFeed_Uitpas $uitpas)
    {
        $this->uitpas = $uitpas;

        $this->kansenTariefForCurrentCardSystem =
            new KansentariefForCurrentCardSystemSpecification();

        $this->kansenTariefForOtherCardSystems =
            new KansentariefForOtherCardSystemsSpecification();

        $this->kansentariefDiscount = new KansentariefDiscountSpecification();

        $this->pointCollecting = new PointCollectingSpecification();
    }

    /**
     * @inheritdoc
     */
    public function getEventInfo($eventId)
    {
        $prices = [];
        $advantages = [];
        $promotions = [];

        $eventQuery =
            new CultureFeed_Uitpas_Event_Query_SearchEventsOptions();

        $eventQuery->cdbid = $eventId;

        $resultSet = $this->uitpas->searchEvents($eventQuery);

        /** @var CultureFeed_Uitpas_Event_CultureEvent $uitpasEvent */
        $uitpasEvent = reset($resultSet->objects);

        if ($uitpasEvent) {
            $advantages = array_merge($advantages, $this->getUitpasAdvantagesFromEvent($uitpasEvent));

            foreach ($uitpasEvent->cardSystems as $cardSystem) {
                foreach ($cardSystem->distributionKeys as $key) {
                    $prices = array_merge($prices, $this->getUitpasPricesFromDistributionKey(
                        $cardSystem,
                        $key
                    ));
                }
            }

            $promotions = $this->getUitpasPointsPromotionsFromEvent($uitpasEvent);
        }
        $advantages = array_unique($advantages);

        return new EventInfo(
            $prices,
            $advantages,
            $promotions
        );
    }

    /**
     * @param CultureFeed_Uitpas_Event_CultureEvent $event
     * @return string[]
     */
    private function getUitpasAdvantagesFromEvent(\CultureFeed_Uitpas_Event_CultureEvent $event)
    {
        $advantages = [];

        if ($this->pointCollecting->isSatisfiedBy($event)) {
            $advantages[] = EventAdvantage::POINT_COLLECTING;
        }

        return $advantages;
    }


    /**
     * @param \CultureFeed_Uitpas_Calendar $uitpasCalendar
     * @return \CultureFeed_Uitpas_Calendar_Period
     */
    public function getDateRangeFromUitpasCalendar(\CultureFeed_Uitpas_Calendar $uitpasCalendar)
    {
        $dateRange = new \CultureFeed_Uitpas_Calendar_Period();

        if (!empty($uitpasCalendar->periods)) {
            /** @var \CultureFeed_Uitpas_Calendar_Period $firstPeriod */
            $firstPeriod = reset($uitpasCalendar->periods);
            $dateRange->datefrom = $firstPeriod->datefrom;

            /** @var \CultureFeed_Uitpas_Calendar_Period $lastPeriod */
            $lastPeriod =  end($uitpasCalendar->periods);
            $dateRange->dateto = $lastPeriod->dateto;
        } else if (!empty($uitpasCalendar->timestamps)) {
            /**
             * The custom Timestamp format for these UiTPAS calendars is a pain
             * to work with. I pick the start and end of the day to determine the
             * actual timestamps. This way events that only span one day
             * are also covered
             */
            /** @var \CultureFeed_Uitpas_Calendar_Timestamp $firstPeriod */
            $firstTimestamp = reset($uitpasCalendar->timestamps);
            $firstTimestampDate = new \DateTime();
            $firstTimestampDate
              ->setTimestamp($firstTimestamp->date)
              ->setTime(0, 0, 0);
            $dateRange->datefrom = $firstTimestampDate->getTimestamp();

            /** @var \CultureFeed_Uitpas_Calendar_Timestamp $lastTimestamp */
            $lastTimestamp =  end($uitpasCalendar->timestamps);
            $lastTimestampDate = new \DateTime();
            $lastTimestampDate
              ->setTimestamp($lastTimestamp->date)
              ->setTime(24, 59, 59);
            $dateRange->dateto = $lastTimestampDate->getTimestamp();
        } else {
            // If there is no useful calendar info, start from the time the
            // export was created.
            $dateRange->datefrom = time();
        }

        return $dateRange;
    }

    /**
     * Get a list of formatted promotions
     *
     * @param \CultureFeed_Uitpas_Event_CultureEvent $event
     * @return string[]
     */
    private function getUitpasPointsPromotionsFromEvent(\CultureFeed_Uitpas_Event_CultureEvent $event)
    {
        $promotions = [];
        /** @var \CultureFeed_Uitpas_Calendar $eventCalendar */
        $eventCalendar = $event->calendar;
        if ($eventCalendar) {
            $dateRange = $this->getDateRangeFromUitpasCalendar($eventCalendar);
        } else {
            $dateRange = new \CultureFeed_Uitpas_Calendar_Period();
            $dateRange->datefrom = time();
        }

        $promotionsQuery = new \CultureFeed_Uitpas_Passholder_Query_SearchPromotionPointsOptions();
        $promotionsQuery->balieConsumerKey = $event->organiserId;
        $promotionsQuery->cashingPeriodBegin = $dateRange->datefrom;
        if ($dateRange->dateto) {
            $promotionsQuery->cashingPeriodBegin = $dateRange->dateto;
        }
        $promotionsQuery->unexpired = true;
        $promotionsQuery->max = 2;

        /** @var \CultureFeed_PointsPromotion[] $promotionsQueryResults */
        $promotionsQueryResults = $this->uitpas->getPromotionPoints($promotionsQuery)->objects;
        foreach ($promotionsQueryResults as $promotionsQueryResult) {
            $promotion = sprintf(
                '%s punten: %s',
                $promotionsQueryResult->points,
                $promotionsQueryResult->title
            );
            $promotions[] = $promotion;
        }

        return $promotions;
    }

    /**
     * @param CultureFeed_Uitpas_CardSystem $cardSystem
     * @param CultureFeed_Uitpas_DistributionKey $key
     * @return array
     */
    private function getUitpasPricesFromDistributionKey(
        CultureFeed_Uitpas_CardSystem $cardSystem,
        CultureFeed_Uitpas_DistributionKey $key
    ) {
        $uitpasPrices = [];

        if ($this->kansenTariefForCurrentCardSystem->isSatisfiedBy($key)) {
            $uitpasPrices[] = [
                'price' => $key->tariff,
                'label' => 'Kansentarief voor ' . $cardSystem->name,
            ];
        }

        if ($this->kansenTariefForOtherCardSystems->isSatisfiedBy($key)) {
            $uitpasPrices[] = [
                'price' => $key->tariff,
                'label' => 'Kansentarief voor kaarthouders uit een andere regio',
            ];
        }

        return $uitpasPrices;
    }

    /**
     * @param CultureFeed_Uitpas_DistributionKey $key
     * @return array
     */
    private function getUitpasAdvantagesFromDistributionKey(CultureFeed_Uitpas_DistributionKey $key)
    {
        $advantages = [];

        if ($this->kansentariefDiscount->isSatisfiedBy($key)) {
            $advantages[] = EventAdvantage::KANSENTARIEF;
        }

        return $advantages;
    }
}
