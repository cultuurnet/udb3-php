<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 20/10/15
 * Time: 10:27
 */

namespace CultuurNet\UDB3\Event\ReadModel\Relations;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Cdb\CdbId\EventCdbIdExtractor;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\OrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated;

class ProjectorTest extends \PHPUnit_Framework_TestCase
{
    const CDBXML_NAMESPACE_33 = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL';

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var Projector
     */
    private $projector;

    public function setUp()
    {
        $this->repository = $this->getMock(
            RepositoryInterface::class
        );

        $this->projector = new Projector(
            $this->repository,
            new EventCdbIdExtractor()
        );
    }

    /**
     * @test
     * @dataProvider cdbXmlDataProvider
     *
     * @param string $aggregateId
     * @param mixed $event
     * @param string $expectedEventId
     * @param string $expectedPlaceId
     * @param string $expectedOrganizerId
     */
    public function it_stores_relations_when_creating_or_updating_events_from_udb2_or_cdbxml(
        $aggregateId,
        $event,
        $expectedEventId,
        $expectedPlaceId,
        $expectedOrganizerId
    ) {
        $this->repository
            ->expects($this->once())
            ->method('storeRelations')
            ->with(
                $this->equalTo($expectedEventId),
                $this->equalTo($expectedPlaceId),
                $this->equalTo($expectedOrganizerId)
            );

        $dateTime = '2015-03-01T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $aggregateId,
            1,
            new Metadata(),
            $event,
            DateTime::fromString($dateTime)
        );

        $this->projector->handle($domainMessage);
    }

    /**
     * @return array
     */
    public function cdbXmlDataProvider()
    {
        $withNone = file_get_contents(__DIR__ . '/event_without_placeid_and_without_organiserid.xml');
        $withPlace = file_get_contents(__DIR__ . '/event_with_placeid_and_without_organiserid.xml');
        $withBoth = file_get_contents(__DIR__ . '/event_with_placeid_and_organiserid.xml');

        return [
            [
                'aggregateId' => 'foo',
                'event' => new EventImportedFromUDB2(
                    'foo',
                    $withNone,
                    self::CDBXML_NAMESPACE_33
                ),
                'expectedEventId' => 'foo',
                'expectedPlaceId' => null,
                'expectedOrganizerId' => null,
            ],
            [
                'aggregateId' => 'foo',
                'event' => new EventImportedFromUDB2(
                    'foo',
                    $withPlace,
                    self::CDBXML_NAMESPACE_33
                ),
                'expectedEventId' => 'foo',
                'expectedPlaceId' => 'bcb983d2-ffba-457d-a023-a821aa841fba',
                'expectedOrganizerId' => null,
            ],
            [
                'aggregateId' => 'foo',
                'event' => new EventImportedFromUDB2(
                    'foo',
                    $withBoth,
                    self::CDBXML_NAMESPACE_33
                ),
                'expectedEventId' => 'foo',
                'expectedPlaceId' => 'bcb983d2-ffba-457d-a023-a821aa841fba',
                'expectedOrganizerId' => 'test-de-bijloke',
            ],
            [
                'aggregateId' => 'foo',
                'event' => new EventUpdatedFromUDB2(
                    'foo',
                    $withNone,
                    self::CDBXML_NAMESPACE_33
                ),
                'expectedEventId' => 'foo',
                'expectedPlaceId' => null,
                'expectedOrganizerId' => null,
            ],
            [
                'aggregateId' => 'foo',
                'event' => new EventUpdatedFromUDB2(
                    'foo',
                    $withPlace,
                    self::CDBXML_NAMESPACE_33
                ),
                'expectedEventId' => 'foo',
                'expectedPlaceId' => 'bcb983d2-ffba-457d-a023-a821aa841fba',
                'expectedOrganizerId' => null,
            ],
            [
                'aggregateId' => 'foo',
                'event' => new EventUpdatedFromUDB2(
                    'foo',
                    $withBoth,
                    self::CDBXML_NAMESPACE_33
                ),
                'expectedEventId' => 'foo',
                'expectedPlaceId' => 'bcb983d2-ffba-457d-a023-a821aa841fba',
                'expectedOrganizerId' => 'test-de-bijloke',
            ],
        ];
    }

    /**
     * @test
     */
    public function it_stores_the_organizer_relation_when_the_organizer_of_an_event_is_updated()
    {
        $eventId = 'event-id';
        $organizerId = 'organizer-id';
        $organizerUpdatedEvent = new OrganizerUpdated($eventId, $organizerId);

        $this->repository
            ->expects($this->once())
            ->method('storeOrganizer')
            ->with(
                $this->equalTo($eventId),
                $this->equalTo($organizerId)
            );

        $domainMessage = new DomainMessage(
            $organizerUpdatedEvent->getItemId(),
            1,
            new Metadata(),
            $organizerUpdatedEvent,
            DateTime::now()
        );

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_stores_the_organizer_relation_when_the_organizer_is_removed_from_an_event()
    {
        $eventId = 'event-id';
        $organizerId = 'organizer-id';
        $organizerDeletedEvent = new OrganizerDeleted($eventId, $organizerId);

        $this->repository
            ->expects($this->once())
            ->method('storeOrganizer')
            ->with(
                $this->equalTo($eventId),
                null
            );

        $domainMessage = new DomainMessage(
            $organizerDeletedEvent->getItemId(),
            1,
            new Metadata(),
            $organizerDeletedEvent,
            DateTime::now()
        );

        $this->projector->handle($domainMessage);
    }
}
