<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\EventInfo;

use \CultureFeed_ResultSet as ResultSet;
use \CultureFeed_Uitpas as Uitpas;
use \CultureFeed_Uitpas_CardSystem as CardSystem;
use \CultureFeed_Uitpas_DistributionKey_Condition as Condition;
use \CultureFeed_Uitpas_Event_Query_SearchEventsOptions as SearchEventsOptions;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\DistributionKey\DistributionKeyConditionFactory;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\DistributionKey\DistributionKeyFactory;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\Event\EventAdvantage;
use CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\Event\EventFactory;

/**
 * Class CultureFeedEventInfoServiceTest
 * @package CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\EventInfo
 */
class CultureFeedEventInfoServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \CultureFeed_Uitpas|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uitpas;

    /**
     * @var EventInfoServiceInterface
     */
    protected $infoService;

    public function setUp()
    {
        $this->uitpas = $this->getMock(Uitpas::class);
        $this->infoService = new CultureFeedEventInfoService($this->uitpas);
    }

    /**
     * @test
     */
    public function it_can_return_multiple_prices_and_advantages()
    {
        // Create an event with a specific id and a point collecting advantage.
        $eventFactory = new EventFactory();
        $eventId = 'd1f0e71d-a9a8-4069-81fb-530134502c58';
        $event = $eventFactory->buildEventWithPoints(1);

        // Set multiple kansentarief discounts (prices).
        $distributionKeyFactory = new DistributionKeyFactory();
        $distributionKeyConditionFactory = new DistributionKeyConditionFactory();
        $distributionKeys = [];
        $distributionKeys[] = $distributionKeyFactory->buildKey(
            2.0,
            [
                $distributionKeyConditionFactory->buildCondition(
                    Condition::DEFINITION_KANSARM,
                    Condition::OPERATOR_IN,
                    Condition::VALUE_MY_CARDSYSTEM
                ),
            ]
        );
        $distributionKeys[] = $distributionKeyFactory->buildKey(
            3.0,
            [
                $distributionKeyConditionFactory->buildCondition(
                    Condition::DEFINITION_KANSARM,
                    Condition::OPERATOR_IN,
                    Condition::VALUE_AT_LEAST_ONE_CARDSYSTEM
                ),
            ]
        );
        $distributionKeys[] = $distributionKeyFactory->buildKey(
            4.0,
            [
                $distributionKeyConditionFactory->buildCondition(
                    Condition::DEFINITION_KANSARM,
                    Condition::OPERATOR_IN,
                    Condition::VALUE_MY_CARDSYSTEM
                ),
            ]
        );

        // Store each kansentarief discount in a separate CardSystem, so we can
        // verify that discounts from the one CardSystem are not overwritten by
        // discounts from other CardSystem objects.
        $event->cardSystems = [];
        $cardSystemId = 0;
        foreach ($distributionKeys as $distributionKey) {
            $cardSystemId++;

            $cardSystem = new CardSystem();
            $cardSystem->id = $cardSystemId;
            $cardSystem->name = 'UiTPAS regio ' . $cardSystemId;
            $cardSystem->distributionKeys = [$distributionKey];

            $event->cardSystems[] = $cardSystem;
        }

        // We will be pretending to search on UiTPAS for this event object.
        $searchEvents = new SearchEventsOptions();
        $searchEvents->cdbid = $eventId;

        // We expect to receive the event object we just instantiated.
        $resultSet = new ResultSet();
        $resultSet->total = 1;
        $resultSet->objects = [$event];

        $promotion = new \CultureFeed_PointsPromotion();
        $promotion->points = 10;
        $promotion->title = 'free drink';

        $onePointPromotion = new \CultureFeed_PointsPromotion();
        $onePointPromotion->points = 1;
        $onePointPromotion->title = 'one point to rule them all';

        $promotionResultSet = new \CultureFeed_ResultSet(1, [$promotion, $onePointPromotion]);

        $this->uitpas->expects($this->once())
            ->method('searchEvents')
            ->with($searchEvents)
            ->willReturn($resultSet);

        $this->uitpas->expects($this->once())
            ->method('getPromotionPoints')
            ->willReturn($promotionResultSet);

        // Request info for the event.
        $eventInfo = $this->infoService->getEventInfo($eventId);
        $prices = $eventInfo->getPrices();
        $advantages = $eventInfo->getAdvantages();
        $promotions = $eventInfo->getPromotions();

        // Make sure we get back the correct prices and advantages.
        $this->assertEquals(
            [
                [
                    'price' => 2,
                    'label' => 'Kansentarief voor UiTPAS regio 1'
                ],
                [
                    'price' => 3,
                    'label' => 'Kansentarief voor kaarthouders uit een andere regio'
                ],
                [
                    'price' => 4,
                    'label' => 'Kansentarief voor UiTPAS regio 3',
                ],
            ],
            $prices
        );

        $this->assertEquals(
            [
                EventAdvantage::POINT_COLLECTING(),
            ],
            $advantages
        );

        $this->assertEquals(
            [
                '10 punten: free drink',
                '1 punt: one point to rule them all'
            ],
            $promotions
        );
    }
}
