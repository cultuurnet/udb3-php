<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Event\Events\LabelAdded as LabelAddedToEvent;
use CultuurNet\UDB3\Event\Events\LabelDeleted as LabelDeletedFromEvent;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\Events\AbstractEvent;
use CultuurNet\UDB3\Label\Events\CopyCreated;
use CultuurNet\UDB3\Label\Events\Created;
use CultuurNet\UDB3\Label\Events\MadeInvisible;
use CultuurNet\UDB3\Label\Events\MadePrivate;
use CultuurNet\UDB3\Label\Events\MadePublic;
use CultuurNet\UDB3\Label\Events\MadeVisible;
use CultuurNet\UDB3\Label\ReadModels\Helper\LabelEventHelper;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\WriteRepositoryInterface;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;
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
     * @var UUID
     */
    private $unknownId;

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
     * @var Entity
     */
    private $entity;

    /**
     * @var Projector
     */
    private $projector;

    protected function setUp()
    {
        $this->uuid = new UUID();
        $this->unknownId = new UUID();

        $this->writeRepository = $this->getMock(
            WriteRepositoryInterface::class
        );

        $this->readRepository = $this->getMock(
            ReadRepositoryInterface::class
        );
        $this->mockGetByUuid();

        $this->labelEventHelper = $this->getMock(
            LabelEventHelper::class,
            [],
            [$this->readRepository]
        );
        $this->mockGetUuid();

        $this->projector = new Projector(
            $this->writeRepository,
            $this->readRepository,
            $this->labelEventHelper
        );
    }

    /**
     * @test
     */
    public function it_handles_created_when_uuid_unique()
    {
        $created = new Created(
            $this->unknownId,
            $this->entity->getName(),
            $this->entity->getVisibility(),
            $this->entity->getPrivacy()
        );

        $this->writeRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->unknownId,
                $this->entity->getName(),
                $this->entity->getVisibility(),
                $this->entity->getPrivacy()
            );

        $domainMessage = $this->createDomainMessage($this->unknownId, $created);

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_does_not_handle_created_when_uuid_not_unique()
    {
        $created = new Created(
            $this->entity->getUuid(),
            $this->entity->getName(),
            $this->entity->getVisibility(),
            $this->entity->getPrivacy()
        );

        $this->writeRepository->expects($this->never())
            ->method('save');

        $domainMessage = $this->createDomainMessage($this->unknownId, $created);

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_handles_copy_created_when_uuid_unique()
    {
        $copyCreated = new CopyCreated(
            $this->unknownId,
            $this->entity->getName(),
            $this->entity->getVisibility(),
            $this->entity->getPrivacy(),
            $this->entity->getParentUuid()
        );

        $this->writeRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->unknownId,
                $this->entity->getName(),
                $this->entity->getVisibility(),
                $this->entity->getPrivacy(),
                $this->entity->getParentUuid()
            );

        $domainMessage = $this->createDomainMessage(
            $this->unknownId,
            $copyCreated
        );

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_does_not_handle_copy_created_when_uuid_not_unique()
    {
        $copyCreated = new CopyCreated(
            $this->entity->getUuid(),
            $this->entity->getName(),
            $this->entity->getVisibility(),
            $this->entity->getPrivacy(),
            $this->entity->getParentUuid()
        );

        $this->writeRepository->expects($this->never())
            ->method('save');

        $domainMessage = $this->createDomainMessage(
            $this->unknownId,
            $copyCreated
        );

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_handles_made_visible()
    {
        $domainMessage = $this->createDomainMessage(
            $this->uuid,
            new MadeVisible($this->uuid)
        );

        $this->writeRepository->expects($this->once())
            ->method('updateVisible')
            ->with($this->uuid);

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_handles_made_invisible()
    {
        $domainMessage = $this->createDomainMessage(
            $this->uuid,
            new MadeInvisible($this->uuid)
        );

        $this->writeRepository->expects($this->once())
            ->method('updateInvisible')
            ->with($this->uuid);

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_handles_made_public()
    {
        $domainMessage = $this->createDomainMessage(
            $this->uuid,
            new MadePublic($this->uuid)
        );

        $this->writeRepository->expects($this->once())
            ->method('updatePublic')
            ->with($this->uuid);

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_handles_made_private()
    {
        $domainMessage = $this->createDomainMessage(
            $this->uuid,
            new MadePrivate($this->uuid)
        );

        $this->writeRepository->expects($this->once())
            ->method('updatePrivate')
            ->with($this->uuid);

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_handles_label_added_to_event()
    {
        $labelAdded = new LabelAddedToEvent(
            'itemId',
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
            'itemId',
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
            'itemId',
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
            'itemId',
            new Label('labelName')
        );

        $this->handleDeleting($labelDeleted);
    }

    private function mockGetByUuid()
    {
        $this->entity = new Entity(
            $this->uuid,
            new StringLiteral('labelName'),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PRIVATE(),
            new UUID()
        );

        $map = [
            [$this->uuid, $this->entity],
            [$this->unknownId, null]
        ];

        $this->readRepository->method('getByUuid')
            ->will($this->returnValueMap($map));
    }

    private function mockGetUuid()
    {
        $this->labelEventHelper->method('getUuid')
            ->will($this->returnValue($this->uuid));
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
        $this->handleLabelMovement($labelAdded, 'updateCountIncrement');

    }

    /**
     * @param AbstractLabelDeleted $labelDeleted
     */
    private function handleDeleting(AbstractLabelDeleted $labelDeleted)
    {
        $this->handleLabelMovement($labelDeleted, 'updateCountDecrement');
    }

    /**
     * @param AbstractLabelEvent $labelEvent
     * @param string $expectedMethod
     */
    private function handleLabelMovement(
        AbstractLabelEvent $labelEvent,
        $expectedMethod
    ) {
        $domainMessage = $this->createDomainMessage(
            $labelEvent->getItemId(),
            $labelEvent
        );

        $this->writeRepository->expects($this->once())
            ->method($expectedMethod)
            ->with($this->uuid);

        $this->projector->handle($domainMessage);
    }
}