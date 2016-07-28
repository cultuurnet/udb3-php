<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Event\Events\LabelAdded as LabelAddedToEvent;
use CultuurNet\UDB3\Event\Events\LabelDeleted as LabelDeletedFromEvent;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\LabelEventOfferTypeResolver;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\WriteRepositoryInterface;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Place\Events\LabelAdded as LabelAddedToPlace;
use CultuurNet\UDB3\Place\Events\LabelDeleted as LabelDeletedFromPlace;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class ProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var string
     */
    private $offerId;

    /**
     * @var WriteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $writeRepository;

    /**
     * @var ReadRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readRepository;

    /**
     * @var LabelEventOfferTypeResolver
     */
    private $offerTypeResolver;

    /**
     * @var Projector
     */
    private $projector;

    protected function setUp()
    {
        $this->uuid = new UUID('A0ED6941-180A-40E3-BD1B-E875FC6D1F25');
        $this->offerId = $this->getOfferId();

        $this->writeRepository = $this->getMock(WriteRepositoryInterface::class);
        $this->readRepository = $this->getMock(ReadRepositoryInterface::class);
        $this->offerTypeResolver = new LabelEventOfferTypeResolver();

        $this->projector = new Projector(
            $this->writeRepository,
            $this->offerTypeResolver
        );
    }

    /**
     * @test
     * @dataProvider labelAddedEventDataProvider
     *
     * @param AbstractLabelAdded $labelAdded
     * @param OfferType $offerType
     */
    public function it_handles_label_added_events(
        AbstractLabelAdded $labelAdded,
        OfferType $offerType
    ) {
        $domainMessage = $this->createDomainMessage(
            $labelAdded->getItemId(),
            $labelAdded
        );

        $this->writeRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->uuid,
                $offerType,
                new StringLiteral($this->offerId)
            );

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     * @dataProvider labelDeletedEventDataProvider
     *
     * @param AbstractLabelDeleted $labelDeleted
     */
    public function it_handles_label_deleted_events(
        AbstractLabelDeleted $labelDeleted
    ) {
        $domainMessage = $this->createDomainMessage(
            $labelDeleted->getItemId(),
            $labelDeleted
        );

        $this->writeRepository->expects($this->once())
            ->method('deleteByUuidAndRelationId')
            ->with($this->uuid, new StringLiteral($labelDeleted->getItemId()));

        $this->projector->handle($domainMessage);
    }

    /**
     * @return array
     */
    public function labelAddedEventDataProvider()
    {
        return [
            [
                new LabelAddedToEvent(
                    $this->getOfferId(),
                    new Label('labelName')
                ),
                OfferType::EVENT(),
            ],
            [
                new LabelAddedToPlace(
                    $this->getOfferId(),
                    new Label('labelName')
                ),
                OfferType::PLACE(),
            ],
        ];
    }

    /**
     * @return array
     */
    public function labelDeletedEventDataProvider()
    {
        return [
            [
                new LabelDeletedFromEvent(
                    $this->getOfferId(),
                    new Label('labelName')
                ),
            ],
            [
                new LabelDeletedFromPlace(
                    $this->getOfferId(),
                    new Label('labelName')
                ),
            ],
        ];
    }

    /**
     * @return string
     */
    private function getOfferId()
    {
        return 'E4CA9DB5-DEE3-42F0-B04A-547DFC3CB2EE';
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
            new Metadata(['labelUuid' => (string) $this->uuid]),
            $payload,
            BroadwayDateTime::now()
        );
    }
}
