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
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\DistributionKey\KansentariefForCurrentCardSystemSpecification;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\DistributionKey\KansentariefForOtherCardSystemsSpecification;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\Event\EventAdvantage;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\Event\PointCollectingSpecification;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\Promotion\EventOrganizerPromotionQueryFactory;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\Promotion\PromotionQueryFactoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class CultureFeedEventInfoService implements EventInfoServiceInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

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
     * @var PointCollectingSpecification
     */
    protected $pointCollecting;

    /**
     * @var PromotionQueryFactoryInterface
     */
    protected $promotionQueryFactory;

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

        $this->pointCollecting = new PointCollectingSpecification();

        $this->promotionQueryFactory = new EventOrganizerPromotionQueryFactory();
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
            $advantages = $this->getUitpasAdvantagesFromEvent($uitpasEvent);

            $prices = $this->getUitpasPricesFromEvent($uitpasEvent);

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
     * @return array
     */
    private function getUitpasPricesFromEvent(CultureFeed_Uitpas_Event_CultureEvent $event)
    {
        $prices = [];

        foreach ($event->cardSystems as $cardSystem) {
            foreach ($cardSystem->distributionKeys as $key) {
                $prices = array_merge($prices, $this->getUitpasPricesFromDistributionKey(
                    $cardSystem,
                    $key
                ));
            }
        }

        return $prices;
    }

    /**
     * @param CultureFeed_Uitpas_Event_CultureEvent $event
     * @return string[]
     */
    private function getUitpasAdvantagesFromEvent(CultureFeed_Uitpas_Event_CultureEvent $event)
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
        $promotionQuery = $this->promotionQueryFactory->createForEvent($event);

        /** @var \CultureFeed_PointsPromotion[] $promotionQueryResults */
        $promotionQueryResults = [];

        try {
            $promotionQueryResults = $this->uitpas->getPromotionPoints($promotionQuery)->objects;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error(
                    'Can\'t retrieve promotions for event with id:' . $event->cdbid,
                    ['exception' => $e]
                );
            }
        };

        foreach ($promotionQueryResults as $promotionsQueryResult) {
            if ($promotionsQueryResult->points === 1) {
                $pointChoice = 'punt';
            } else {
                $pointChoice = 'punten';
            }

            $promotion = sprintf(
                '%s %s: %s',
                $promotionsQueryResult->points,
                $pointChoice,
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
}
