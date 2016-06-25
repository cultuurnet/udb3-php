<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Event\Events\LabelAdded as LabelAddedToEvent;
use CultuurNet\UDB3\Event\Events\LabelDeleted as LabelDeletedFromEvent;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\ReadModels\Helper\LabelEventHelper;
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
    const RELATION_ID = 'relationId';

    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var WriteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $writeRepository;

    /**
     * @var ReadRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readRepository;

    /**
     * @var LabelEventHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $labelEventHelper;

    /**
     * @var Projector
     */
    private $projector;

    protected function setUp()
    {
        $this->uuid = new UUID();

        $this->writeRepository = $this->getMock(
            WriteRepositoryInterface::class
        );

        $this->readRepository = $this->getMock(
            ReadRepositoryInterface::class
        );

        $this->labelEventHelper = $this->getMock(
            LabelEventHelper::class,
            [],
            [$this->readRepository]
        );
        $this->mockLabelEventHelper();

        $this->projector = new Projector(
            $this->writeRepository,
            $this->labelEventHelper
        );
    }

    /**
     * @test
     */
    public function it_handles_label_added_to_event()
    {
        $labelAdded = new LabelAddedToEvent(
            self::RELATION_ID,
            new Label('labelName')
        );

        $this->handleAdding($labelAdded);
    }

    /**
     * @test
     */
    public function it_handles_label_deleted_from_event()
    {
        $labelDeleted = new LabelDeletedFromEvent(
            self::RELATION_ID,
            new Label('labelName')
        );

        $this->handleDeleting($labelDeleted);
    }

    /**
     * @test
     */
    public function it_handles_label_added_to_place()
    {
        $labelAdded = new LabelAddedToPlace(
            self::RELATION_ID,
            new Label('labelName')
        );

        $this->handleAdding($labelAdded);
    }

    /**
     * @test
     */
    public function it_handles_label_deleted_from_place()
    {
        $labelDeleted = new LabelDeletedFromPlace(
            self::RELATION_ID,
            new Label('labelName')
        );

        $this->handleDeleting($labelDeleted);
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

    /**
     * @param AbstractLabelAdded $labelAdded
     */
    private function handleAdding(AbstractLabelAdded $labelAdded)
    {
        $domainMessage = $this->createDomainMessage(
            $labelAdded->getItemId(),
            $labelAdded
        );

        $expectedRelationType = $this->getRelationType($labelAdded);

        $this->writeRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->uuid,
                new StringLiteral('labelName'),
                $expectedRelationType,
                new StringLiteral(self::RELATION_ID)
            );

        $this->projector->handle($domainMessage);
    }

    /**
     * @param AbstractLabelDeleted $labelDeleted
     */
    private function handleDeleting(AbstractLabelDeleted $labelDeleted)
    {
        $domainMessage = $this->createDomainMessage(
            $labelDeleted->getItemId(),
            $labelDeleted
        );

        $this->writeRepository->expects($this->once())
            ->method('deleteByUuidAndRelationId')
            ->with($this->uuid, new StringLiteral($labelDeleted->getItemId()));

        $this->projector->handle($domainMessage);
    }

    private function mockLabelEventHelper()
    {
        $this->mockGetUuid();
        $this->mockGetRelationType();
        $this->mockGetRelationId();
    }

    private function mockGetUuid()
    {
        $this->labelEventHelper->method('getUuid')
            ->willReturn($this->uuid);
    }

    private function mockGetRelationType()
    {
        $this->labelEventHelper->method('getRelationType')
            ->willReturnCallback(function ($labelEvent) {
                return $this->getRelationType($labelEvent);
            });
    }

    private function mockGetRelationId()
    {
        $this->labelEventHelper->method('getRelationId')
            ->willReturn(new StringLiteral(self::RELATION_ID));
    }

    /**
     * @param AbstractLabelEvent $labelEvent
     * @return OfferType|null
     */
    private function getRelationType(AbstractLabelEvent $labelEvent)
    {
        $relationType = null;

        if ($labelEvent instanceof LabelAddedToPlace ||
            $labelEvent instanceof LabelDeletedFromPlace) {
            $relationType = OfferType::PLACE();
        } else if ($labelEvent instanceof LabelAddedToEvent ||
            $labelEvent instanceof LabelDeletedFromEvent) {
            $relationType = OfferType::EVENT();
        }

        return $relationType;
    }
}
