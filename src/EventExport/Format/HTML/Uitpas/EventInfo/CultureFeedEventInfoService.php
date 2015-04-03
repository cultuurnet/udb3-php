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
use CultuurNet\UDB3\Event\EventType;
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

        $searchEvents =
            new CultureFeed_Uitpas_Event_Query_SearchEventsOptions();

        $searchEvents->cdbid = $eventId;

        $resultSet = $this->uitpas->searchEvents(
            $searchEvents
        );

        /** @var CultureFeed_Uitpas_Event_CultureEvent $uitpasEvent */
        $uitpasEvent = reset($resultSet->objects);

        if ($uitpasEvent) {
            $advantages += $this->getUitpasAdvantagesFromEvent($uitpasEvent);

            foreach ($uitpasEvent->cardSystems as $cardSystem) {
                foreach ($cardSystem->distributionKeys as $key) {
                    $prices += $this->getUitpasPricesFromDistributionKey(
                        $cardSystem,
                        $key
                    );

                    $advantages += $this->getUitpasAdvantagesFromDistributionKey($key);
                }
            }
        }

        return new EventInfo(
            $prices,
            $advantages
        );
    }

    /**
     * @param CultureFeed_Uitpas_Event_CultureEvent $event
     */
    private function getUitpasAdvantagesFromEvent(\CultureFeed_Uitpas_Event_CultureEvent $event)
    {
        $advantages = [];

        if ($this->pointCollecting->isSatisfiedBy($event)) {
            $advantages[] = EventAdvantage::POINT_COLLECTING;
        }

        return $advantages;
    }

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
