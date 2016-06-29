<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Label\Events\AbstractEvent;
use CultuurNet\UDB3\Label\Events\MadeVisible;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\OfferLabelRelation;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use PHPUnit_Framework_MockObject_MockObject;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class OfferLabelProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OfferLabelProjector
     */
    private $projector;

    /**
     * @var DocumentRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $offerRepository;

    /**
     * @var ReadRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $relationRepository;

    protected function setUp()
    {
        $this->relationRepository = $this->getMock(ReadRepositoryInterface::class);
        $this->offerRepository = $this->getMock(DocumentRepositoryInterface::class);

        $this->projector = new OfferLabelProjector(
            $this->offerRepository,
            $this->relationRepository
        );
    }

    private function mockRelatedPlaceDocument(UUID $labelId, JsonDocument $jsonDocument)
    {
        $this->relationRepository
            ->expects($this->once())
            ->method('getOfferLabelRelations')
            ->with($labelId)
            ->willReturn(
                [
                    new OfferLabelRelation(
                        $labelId,
                        new StringLiteral('black'),
                        OfferType::PLACE(),
                        new StringLiteral($jsonDocument->getId())
                    ),
                ]
            );

        $this->offerRepository
            ->expects($this->once())
            ->method('get')
            ->with($jsonDocument->getId())
            ->willReturn($jsonDocument);
    }

    /**
     * @test
     */
    public function it_should_update_the_projection_of_offers_which_have_a_label_made_visible()
    {
        $labelId = new UUID();
        $placeId = new StringLiteral('B8A3FF1E-64A3-41C4-A2DB-A6FA35E4219A');
        $madeVisibleEvent = new MadeVisible($labelId);

        $existingPlaceDocument = new JsonDocument(
            (string)$placeId,
            json_encode((object)['labels' => ['orange'], 'hiddenLabels' => ['green', 'black']])
        );

        $this->mockRelatedPlaceDocument($labelId, $existingPlaceDocument);

        $domainMessage = $this->createDomainMessage(
            (string)$labelId,
            $madeVisibleEvent
        );

        $expectedDocument = new JsonDocument(
            (string) $placeId,
            json_encode((object)['labels' => ['orange', 'black'], 'hiddenLabels' => ['green']])
        );

        $this->offerRepository
            ->expects($this->once())
            ->method('save')
            ->with($expectedDocument);

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_should_remove_the_hidden_labels_property_of_an_offer_when_the_last_hidden_label_is_made_visible()
    {
        $labelId = new UUID();
        $placeId = new StringLiteral('B8A3FF1E-64A3-41C4-A2DB-A6FA35E4219A');
        $madeVisibleEvent = new MadeVisible($labelId);

        $existingPlaceDocument = new JsonDocument(
            (string) $placeId,
            json_encode((object)['labels' => ['orange', 'green'], 'hiddenLabels' => ['black']])
        );

        $this->mockRelatedPlaceDocument($labelId, $existingPlaceDocument);

        $domainMessage = $this->createDomainMessage(
            (string) $labelId,
            $madeVisibleEvent
        );

        $expectedDocument = new JsonDocument(
            (string) $placeId,
            json_encode((object)['labels' => ['orange', 'green', 'black']])
        );

        $this->offerRepository
            ->expects($this->once())
            ->method('save')
            ->with($expectedDocument);

        $this->projector->handle($domainMessage);
    }

    /**
     * @param string $id
     * @param AbstractEvent|AbstractLabelEvent $payload
     * @return DomainMessage
     */
    private function createDomainMessage($id, $payload)
    {
        return new DomainMessage(
            $id,
            0,
            new Metadata(),
            $payload,
            BroadwayDateTime::now()
        );
    }
}
