<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\HTML;

use CultureFeed_Uitpas;
use CultureFeed_Uitpas_CardSystem;
use CultureFeed_Uitpas_DistributionKey;
use CultureFeed_Uitpas_Event_CultureEvent;
use CultureFeed_Uitpas_Event_Query_SearchEventsOptions;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\DistributionKeySpecification;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\KansentariefForCurrentCardSystemSpecification;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\KansentariefForOtherCardSystemsSpecification;

class CultureFeedUitpasEventInfoService implements UitpasEventInfoServiceInterface
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
     * @param CultureFeed_Uitpas $uitpas
     */
    public function __construct(CultureFeed_Uitpas $uitpas)
    {
        $this->uitpas = $uitpas;

        $this->kansenTariefForCurrentCardSystem =
            new KansentariefForCurrentCardSystemSpecification();

        $this->kansenTariefForOtherCardSystems =
            new KansentariefForOtherCardSystemsSpecification();
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
            foreach ($uitpasEvent->cardSystems as $cardSystem) {
                foreach ($cardSystem->distributionKeys as $key) {
                    $prices += $this->getUitpasPricesFromDistributionKey(
                        $cardSystem,
                        $key
                    );
                }
            }
        }

        return new UitpasEventInfo(
            $prices,
            $advantages
        );
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

        foreach ($uitpasPrices as &$tariff) {
            if ($tariff['price'] === '0.0') {
                $tariff['price'] = 'Gratis';
            }
        }

        return $uitpasPrices;
    }
}
