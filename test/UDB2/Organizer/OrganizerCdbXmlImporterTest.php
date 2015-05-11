<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\Organizer;

use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Organizer\Organizer;
use CultuurNet\UDB3\Organizer\OrganizerRepository;
use CultuurNet\UDB3\UDB2\ActorCdbXmlServiceInterface;
use CultuurNet\UDB3\UDB2\ActorNotFoundException;
use Psr\Log\LoggerInterface;

class OrganizerCdbXmlImporterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrganizerCdbXmlImporter
     */
    private $importer;

    /**
     * @var OrganizerRepository
     */
    private $repository;

    /**
     * @var ActorCdbXmlServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actorCdbXmlService;

    /**
     * @var TraceableEventStore
     */
    private $store;

    public function setUp()
    {
        $this->store = new TraceableEventStore(
            new InMemoryEventStore()
        );

        /** @var EventBusInterface $eventBus */
        $eventBus = $this->getMock(
            EventBusInterface::class
        );

        $this->repository = new OrganizerRepository(
            $this->store,
            $eventBus,
            []
        );

        $this->actorCdbXmlService = $this->getMock(ActorCdbXmlServiceInterface::class);

        $this->importer = new OrganizerCdbXmlImporter(
            $this->actorCdbXmlService,
            $this->repository
        );
    }

    /**
     * @test
     */
    public function it_creates_an_organizer_from_cdbxml()
    {
        $this->store->trace();

        $organizerId = '404EE8DE-E828-9C07-FE7D12DC4EB24480';

        $cdbXml = file_get_contents(__DIR__ . '/samples/organizer.xml');
        $cdbXmlNamespaceUri = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL';

        $this->actorCdbXmlService->expects($this->once())
            ->method('getCdbXmlOfActor')
            ->willReturn($cdbXml);

        $this->actorCdbXmlService->expects($this->atLeastOnce())
            ->method('getCdbXmlNamespaceUri')
            ->willReturn($cdbXmlNamespaceUri);

        $organizer = $this->importer->createOrganizerFromUDB2($organizerId);

        $this->assertInstanceOf(Organizer::class, $organizer);

        $this->assertTracedEvents(
            [
                new OrganizerImportedFromUDB2(
                    $organizerId,
                    $cdbXml,
                    $cdbXmlNamespaceUri
                ),
            ]
        );
    }

    /**
     * @test
     */
    public function it_returns_nothing_if_creation_failed()
    {
        $this->actorCdbXmlService->expects($this->once())
            ->method('getCdbXmlOfActor')
            ->willThrowException(new ActorNotFoundException());

        $organizer = $this->importer->createOrganizerFromUDB2('foo');

        $this->assertNull($organizer);
    }

    /**
     * @test
     */
    public function it_logs_creation_failures()
    {
        $exception = new ActorNotFoundException();

        $this->actorCdbXmlService->expects($this->once())
            ->method('getCdbXmlOfActor')
            ->willThrowException($exception);

        $logger = $this->getMock(LoggerInterface::class);
        $this->importer->setLogger($logger);

        $logger->expects($this->once())
            ->method('notice')
            ->with(
                'Organizer creation in UDB3 failed with an exception',
                [
                    'exception' => $exception,
                    'organizerId' => 'foo',
                ]
            );

        $this->importer->createOrganizerFromUDB2('foo');
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
