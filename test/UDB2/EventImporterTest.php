<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use Broadway\ReadModel\InMemory\InMemoryRepository;
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3\Event\EventRepository;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\OrganizerServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\PlaceServiceInterface;
use CultuurNet\UDB3\Event\Title;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\PlaceService;

class EventImporterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventImporter
     */
    protected $importer;

    /**
     * @var EventRepository
     */
    protected $repository;

    /**
     * @var TraceableEventStore
     */
    protected $store;

    /**
     * @var EventCdbXmlServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventCdbXmlService;

    /**
     * @var PlaceServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $placeService;

    /**
     * @var OrganizerServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $organizerService;

    public function setUp()
    {
        $this->store = new TraceableEventStore(
            new InMemoryEventStore()
        );

        /** @var EventBusInterface $eventBus */
        $eventBus = $this->getMock(
            EventBusInterface::class
        );

        $this->repository = new EventRepository(
            $this->store,
            $eventBus,
            []
        );

        $this->eventCdbXmlService = $this->getMock(
            EventCdbXmlServiceInterface::class
        );

        $this->eventCdbXmlService->expects($this->any())
            ->method('getCdbXmlNamespaceUri')
            ->willReturn('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL');

        $this->placeService = $this->getMock(
            PlaceService::class,
            [],
            [],
            '',
            false
        );

        $this->organizerService = $this->getMock(
            OrganizerService::class,
            [],
            [],
            '',
            false
        );

        $this->importer = new EventImporter(
            $this->eventCdbXmlService,
            $this->repository,
            $this->placeService,
            $this->organizerService
        );
    }

    /**
     * @test
     */
    public function it_updates_an_existing_event_with_cdbxml()
    {
        $cdbId = '7914ed2d-9f28-4946-b9bd-ae8f7a4aea11';

        $event = Event::create(
            $cdbId,
            new Title('Infodag Sint-Lukas Brussel'),
            '7914ed2d-9f28-4946-b9bd-ae8f7a4aea22',
            new \DateTime(),
            new EventType(
                '0.12.0.0.0',
                'Opendeurdag'
            )
        );
        $this->repository->save($event);

        $eventXml = file_get_contents(
            __DIR__ . '/search-results-single-event.xml'
        );

        $this->eventCdbXmlService
            ->expects($this->once())
            ->method('getCdbXmlOfEvent')
            ->with($cdbId)
            ->willReturn($eventXml);

        $this->store->trace();

        $this->importer->updateEventFromUDB2($cdbId);

        $this->assertTracedEvents(
            [
                new EventUpdatedFromUDB2(
                    $cdbId,
                    $eventXml,
                    $this->eventCdbXmlService->getCdbXmlNamespaceUri()
                ),
            ]
        );
    }

    /**
     * @param object[] $expectedEvents
     */
    protected function assertTracedEvents($expectedEvents)
    {
        $events = $this->store->getEvents();

        $this->assertEquals(
            $expectedEvents,
            $events
        );
    }
}
