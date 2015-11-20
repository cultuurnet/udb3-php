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
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\OrganizerDeleted;
use CultuurNet\UDB3\Event\Events\OrganizerUpdated;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\EventXmlString;
use ValueObjects\String\String;

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

    /**
     * @var EventServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventService;

    public function setUp()
    {
        $this->repository = $this->getMock(
            RepositoryInterface::class
        );

        $this->eventService = $this->getMock(
            EventServiceInterface::class
        );

        $this->projector = new Projector(
            $this->repository,
            $this->eventService
        );
    }

    /**
     * @test
     */
    public function it_stores_relations_when_creating_event_from_cdbxml_with_place_and_without_organizer()
    {
        $xml = file_get_contents(__DIR__ . '/event_with_placeid_and_without_organiserid.xml');

        $eventCreatedFromCdbXml = new EventCreatedFromCdbXml(
            new String('foo'),
            new EventXmlString($xml),
            new String(self::CDBXML_NAMESPACE_33)
        );

        $expectedEventId = 'foo';
        $expectedPlaceId = 'bcb983d2-ffba-457d-a023-a821aa841fba';
        $expectedOrganizerId = null;

        $this->repository
            ->expects($this->once())
            ->method('storeRelations')
            ->with(
                $this->equalTo($expectedEventId),
                $this->equalTo($expectedPlaceId),
                $this->equalTo($expectedOrganizerId)
            );

        $importedDate = '2015-03-01T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $eventCreatedFromCdbXml->getEventId()->toNative(),
            1,
            new Metadata(),
            $eventCreatedFromCdbXml,
            DateTime::fromString($importedDate)
        );

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_stores_relations_when_creating_event_from_cdbxml_with_place_and_organizer()
    {
        $xml = file_get_contents(__DIR__ . '/event_with_placeid_and_organiserid.xml');

        $eventCreatedFromCdbXml = new EventCreatedFromCdbXml(
            new String('foo'),
            new EventXmlString($xml),
            new String(self::CDBXML_NAMESPACE_33)
        );

        $expectedEventId = 'foo';
        $expectedPlaceId = 'bcb983d2-ffba-457d-a023-a821aa841fba';
        $expectedOrganizerId = 'test-de-bijloke';

        $this->repository
            ->expects($this->once())
            ->method('storeRelations')
            ->with(
                $this->equalTo($expectedEventId),
                $this->equalTo($expectedPlaceId),
                $this->equalTo($expectedOrganizerId)
            );

        $importedDate = '2015-03-01T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $eventCreatedFromCdbXml->getEventId()->toNative(),
            1,
            new Metadata(),
            $eventCreatedFromCdbXml,
            DateTime::fromString($importedDate)
        );

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_stores_relations_when_creating_event_from_cdbxml_without_place_and_without_organizer()
    {
        $xml = file_get_contents(__DIR__ . '/event_without_placeid_and_without_organiserid.xml');

        $eventCreatedFromCdbXml = new EventCreatedFromCdbXml(
            new String('foo'),
            new EventXmlString($xml),
            new String(self::CDBXML_NAMESPACE_33)
        );

        $expectedEventId = 'foo';
        $expectedPlaceId = null;
        $expectedOrganizerId = null;

        $this->repository
            ->expects($this->once())
            ->method('storeRelations')
            ->with(
                $this->equalTo($expectedEventId),
                $this->equalTo($expectedPlaceId),
                $this->equalTo($expectedOrganizerId)
            );

        $importedDate = '2015-03-01T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $eventCreatedFromCdbXml->getEventId()->toNative(),
            1,
            new Metadata(),
            $eventCreatedFromCdbXml,
            DateTime::fromString($importedDate)
        );

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_stores_relations_when_updating_event_from_cdbxml_with_place_and_without_organizer()
    {
        $xml = file_get_contents(__DIR__ . '/event_with_placeid_and_without_organiserid.xml');

        $eventUpdatedFromCdbXml = new EventUpdatedFromCdbXml(
            new String('foo'),
            new EventXmlString($xml),
            new String(self::CDBXML_NAMESPACE_33)
        );

        $expectedEventId = 'foo';
        $expectedPlaceId = 'bcb983d2-ffba-457d-a023-a821aa841fba';
        $expectedOrganizerId = null;

        $this->repository
            ->expects($this->once())
            ->method('storeRelations')
            ->with(
                $this->equalTo($expectedEventId),
                $this->equalTo($expectedPlaceId),
                $this->equalTo($expectedOrganizerId)
            );

        $importedDate = '2015-03-01T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $eventUpdatedFromCdbXml->getEventId()->toNative(),
            1,
            new Metadata(),
            $eventUpdatedFromCdbXml,
            DateTime::fromString($importedDate)
        );

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_stores_relations_when_updating_event_from_cdbxml_with_place_and_organizer()
    {
        $xml = file_get_contents(__DIR__ . '/event_with_placeid_and_organiserid.xml');

        $eventUpdatedFromCdbXml = new EventUpdatedFromCdbXml(
            new String('foo'),
            new EventXmlString($xml),
            new String(self::CDBXML_NAMESPACE_33)
        );

        $expectedEventId = 'foo';
        $expectedPlaceId = 'bcb983d2-ffba-457d-a023-a821aa841fba';
        $expectedOrganizerId = 'test-de-bijloke';

        $this->repository
            ->expects($this->once())
            ->method('storeRelations')
            ->with(
                $this->equalTo($expectedEventId),
                $this->equalTo($expectedPlaceId),
                $this->equalTo($expectedOrganizerId)
            );

        $importedDate = '2015-03-01T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $eventUpdatedFromCdbXml->getEventId()->toNative(),
            1,
            new Metadata(),
            $eventUpdatedFromCdbXml,
            DateTime::fromString($importedDate)
        );

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_stores_relations_when_updating_event_from_cdbxml_without_place_and_without_organizer()
    {
        $xml = file_get_contents(__DIR__ . '/event_without_placeid_and_without_organiserid.xml');

        $eventUpdatedFromCdbXml = new EventUpdatedFromCdbXml(
            new String('foo'),
            new EventXmlString($xml),
            new String(self::CDBXML_NAMESPACE_33)
        );

        $expectedEventId = 'foo';
        $expectedPlaceId = null;
        $expectedOrganizerId = null;

        $this->repository
            ->expects($this->once())
            ->method('storeRelations')
            ->with(
                $this->equalTo($expectedEventId),
                $this->equalTo($expectedPlaceId),
                $this->equalTo($expectedOrganizerId)
            );

        $importedDate = '2015-03-01T10:17:19.176169+02:00';

        $domainMessage = new DomainMessage(
            $eventUpdatedFromCdbXml->getEventId()->toNative(),
            1,
            new Metadata(),
            $eventUpdatedFromCdbXml,
            DateTime::fromString($importedDate)
        );

        $this->projector->handle($domainMessage);
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
            $organizerUpdatedEvent->getEventId(),
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
            $organizerDeletedEvent->getEventId(),
            1,
            new Metadata(),
            $organizerDeletedEvent,
            DateTime::now()
        );

        $this->projector->handle($domainMessage);
    }
}
