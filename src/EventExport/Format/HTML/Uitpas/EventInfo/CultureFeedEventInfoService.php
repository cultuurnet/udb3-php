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
     * Get a list of formatted promotions
     *
     * @param \CultureFeed_Uitpas_Event_CultureEvent $event
     * @return string[]
     */
    private function getUitpasPointsPromotionsFromEvent(\CultureFeed_Uitpas_Event_CultureEvent $event)
    {
        $promotions = [];

        $promotionsQuery = new \CultureFeed_Uitpas_Passholder_Query_SearchPromotionPointsOptions();
        $promotionsQuery->balieConsumerKey = $event->organiserId;
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
