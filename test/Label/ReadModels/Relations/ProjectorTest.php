<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\LabelAdded as LabelAddedToEvent;
use CultuurNet\UDB3\Event\Events\LabelRemoved as LabelRemovedFromEvent;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\LabelEventRelationTypeResolver;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\WriteRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelRemoved;
use CultuurNet\UDB3\Organizer\Events\LabelAdded as LabelAddedToOrganizer;
use CultuurNet\UDB3\Organizer\Events\LabelAdded;
use CultuurNet\UDB3\Organizer\Events\LabelRemoved as LabelRemovedFromOrganizer;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Organizer\Events\OrganizerUpdatedFromUDB2;
use CultuurNet\UDB3\Place\Events\LabelAdded as LabelAddedToPlace;
use CultuurNet\UDB3\Place\Events\LabelRemoved as LabelRemovedFromPlace;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceUpdatedFromUDB2;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

class ProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var LabelName
     */
    private $labelName;

    /**
     * @var string
     */
    private $relationId;

    /**
     * @var WriteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $writeRepository;

    /**
     * @var ReadRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readRepository;

    /**
     * @var LabelEventRelationTypeResolver
     */
    private $offerTypeResolver;

    /**
     * @var Projector
     */
    private $projector;

    protected function setUp()
    {
        $this->uuid = new UUID('A0ED6941-180A-40E3-BD1B-E875FC6D1F25');
        $this->labelName = new LabelName('labelName');

        $this->relationId = $this->getRelationId();

        $this->writeRepository = $this->createMock(WriteRepositoryInterface::class);
        $this->readRepository = $this->createMock(ReadRepositoryInterface::class);
        $this->offerTypeResolver = new LabelEventRelationTypeResolver();

        $this->projector = new Projector(
            $this->writeRepository,
            $this->offerTypeResolver
        );
    }

    /**
     * @test
     * @dataProvider labelAddedEventDataProvider
     *
     * @param string $relationId
     * @param AbstractLabelAdded|LabelAdded $labelAdded
     * @param RelationType $relationType
     */
    public function it_handles_label_added_events(
        $relationId,
        $labelAdded,
        RelationType $relationType
    ) {
        $domainMessage = $this->createDomainMessage(
            $relationId,
            $labelAdded
        );

        $this->writeRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->labelName,
                $relationType,
                new StringLiteral($this->relationId)
            );

        $this->projector->handle($domainMessage);
    }

    /**
     * @return array
     */
    public function labelAddedEventDataProvider()
    {
        return [
            [
                $this->getRelationId(),
                new LabelAddedToEvent(
                    $this->getRelationId(),
                    new Label('labelName')
                ),
                RelationType::EVENT(),
            ],
            [
                $this->getRelationId(),
                new LabelAddedToPlace(
                    $this->getRelationId(),
                    new Label('labelName')
                ),
                RelationType::PLACE(),
            ],
            [
                $this->getRelationId(),
                new LabelAddedToOrganizer(
                    $this->getRelationId(),
                    new Label('labelName')
                ),
                RelationType::ORGANIZER(),
            ]
        ];
    }

    /**
     * @test
     * @dataProvider labelRemovedEventDataProvider
     *
     * @param string $relationId
     * @param AbstractLabelRemoved|LabelRemovedFromOrganizer $labelRemoved
     */
    public function it_handles_label_deleted_events(
        $relationId,
        $labelRemoved
    ) {
        $domainMessage = $this->createDomainMessage(
            $relationId,
            $labelRemoved
        );

        $this->writeRepository->expects($this->once())
            ->method('deleteByLabelNameAndRelationId')
            ->with($this->labelName, new StringLiteral($relationId));

        $this->projector->handle($domainMessage);
    }

    /**
     * @return array
     */
    public function labelRemovedEventDataProvider()
    {
        return [
            [
                $this->getRelationId(),
                new LabelRemovedFromEvent(
                    $this->getRelationId(),
                    new Label('labelName')
                ),
            ],
            [
                $this->getRelationId(),
                new LabelRemovedFromPlace(
                    $this->getRelationId(),
                    new Label('labelName')
                ),
            ],
            [
                $this->getRelationId(),
                new LabelRemovedFromOrganizer(
                    $this->getRelationId(),
                    new Label('labelName')
                ),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider fromUdb2DataProvider
     *
     * @param StringLiteral $itemId
     * @param SerializableInterface $payload
     * @param RelationType $relationType
     */
    public function it_handles_import_and_update_events_from_udb2(
        StringLiteral $itemId,
        SerializableInterface $payload,
        RelationType $relationType
    ) {
        $domainMessage = $this->createDomainMessage(
            $itemId->toNative(),
            $payload
        );

        $this->writeRepository->expects($this->at(0))
            ->method('deleteImportedByRelationId')
            ->with($itemId);

        $this->writeRepository->expects($this->at(1))
            ->method('save')
            ->with(
                new LabelName('2dotstwice'),
                $relationType,
                $itemId,
                true
            );

        $this->writeRepository->expects($this->at(2))
            ->method('save')
            ->with(
                new LabelName('cultuurnet'),
                $relationType,
                $itemId,
                true
            );

        $this->projector->handle($domainMessage);
    }

    /**
     * @return array
     */
    public function fromUdb2DataProvider()
    {
        $itemId = new StringLiteral('d53c2bc9-8f0e-4c9a-8457-77e8b3cab3d1');
        $cdbXmlNamespaceUri = \CultureFeed_Cdb_Xml::namespaceUriForVersion('3.3');

        return [
            [
                $itemId,
                new EventImportedFromUDB2(
                    $itemId->toNative(),
                    file_get_contents(__DIR__ . '/Samples/event.xml'),
                    $cdbXmlNamespaceUri
                ),
                RelationType::EVENT(),
            ],
            [
                $itemId,
                new PlaceImportedFromUDB2(
                    $itemId->toNative(),
                    file_get_contents(__DIR__ . '/Samples/place.xml'),
                    $cdbXmlNamespaceUri
                ),
                RelationType::PLACE(),
            ],
            [
                $itemId,
                new OrganizerImportedFromUDB2(
                    $itemId->toNative(),
                    file_get_contents(__DIR__ . '/Samples/organizer.xml'),
                    $cdbXmlNamespaceUri
                ),
                RelationType::ORGANIZER(),
            ],
            [
                $itemId,
                new EventUpdatedFromUDB2(
                    $itemId->toNative(),
                    file_get_contents(__DIR__ . '/Samples/event.xml'),
                    $cdbXmlNamespaceUri
                ),
                RelationType::EVENT(),
            ],
            [
                $itemId,
                new PlaceUpdatedFromUDB2(
                    $itemId->toNative(),
                    file_get_contents(__DIR__ . '/Samples/place.xml'),
                    $cdbXmlNamespaceUri
                ),
                RelationType::PLACE(),
            ],
            [
                $itemId,
                new OrganizerUpdatedFromUDB2(
                    $itemId->toNative(),
                    file_get_contents(__DIR__ . '/Samples/organizer.xml'),
                    $cdbXmlNamespaceUri
                ),
                RelationType::ORGANIZER(),
            ],
            [
                $itemId,
                new OrganizerUpdatedFromUDB2(
                    $itemId->toNative(),
                    file_get_contents(__DIR__ . '/Samples/organizer_with_same_label_but_different_casing.xml'),
                    $cdbXmlNamespaceUri
                ),
                RelationType::ORGANIZER(),
            ],
            [
                $itemId,
                new OrganizerUpdatedFromUDB2(
                    $itemId->toNative(),
                    file_get_contents(__DIR__ . '/Samples/organizer_with_same_label_but_different_casing_and_visibility.xml'),
                    $cdbXmlNamespaceUri
                ),
                RelationType::ORGANIZER(),
            ],
        ];
    }

    /**
     * @return string
     */
    private function getRelationId()
    {
        return 'E4CA9DB5-DEE3-42F0-B04A-547DFC3CB2EE';
    }

    /**
     * @param string $id
     * @param SerializableInterface $payload
     * @return DomainMessage
     */
    private function createDomainMessage($id, SerializableInterface $payload)
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
