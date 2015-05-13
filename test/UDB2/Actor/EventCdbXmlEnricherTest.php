<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\Actor;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventHandling\TraceableEventBus;
use CultuurNet\UDB2DomainEvents\ActorCreated;
use CultuurNet\UDB2DomainEvents\ActorUpdated;
use CultuurNet\UDB3\UDB2\Actor\Events\ActorCreatedEnrichedWithCdbXml;
use CultuurNet\UDB3\UDB2\Actor\Events\ActorUpdatedEnrichedWithCdbXml;
use CultuurNet\UDB3\UDB2\ActorCdbXmlServiceInterface;
use CultuurNet\UDB3\UDB2\OutdatedXmlRepresentationException;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

class EventCdbXmlEnricherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TraceableEventBus
     */
    private $eventBus;

    /**
     * @var ActorCdbXmlServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cdbXmlService;

    /**
     * @var EventCdbXmlEnricher
     */
    private $enricher;

    public function setUp()
    {
        $this->eventBus = new TraceableEventBus(
            new SimpleEventBus()
        );

        $this->eventBus->trace();

        $this->cdbXmlService = $this->getMock(
            ActorCdbXmlServiceInterface::class
        );

        $this->cdbXmlService->expects($this->any())
            ->method('getCdbXmlNamespaceUri')
            ->willReturn($this->cdbXmlNamespaceUri());

        $this->enricher = new EventCdbXmlEnricher(
            $this->cdbXmlService,
            $this->eventBus
        );
    }

    private function cdbXml()
    {
        return file_get_contents(__DIR__ . '/Events/actor.xml');
    }

    private function cdbXmlNamespaceUri()
    {
        return 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL';
    }

    /**
     * Data provider with for each incoming message a corresponding expected new
     * message.
     */
    public function messagesProvider()
    {
        $actorCreated = $this->newActorCreated(
            new \DateTimeImmutable('2013-07-18T09:04:37')
        );

        $actorUpdated = $this->newActorUpdated(
            new \DateTimeImmutable('2013-07-18T09:04:37')
        );

        return [
            [
                $actorCreated,
                new ActorCreatedEnrichedWithCdbXml(
                    $actorCreated->getActorId(),
                    $actorCreated->getTime(),
                    $actorCreated->getAuthor(),
                    new String($this->cdbXml()),
                    new String($this->cdbXmlNamespaceUri())
                )
            ],
            [
                $actorUpdated,
                new ActorUpdatedEnrichedWithCdbXml(
                    $actorUpdated->getActorId(),
                    $actorUpdated->getTime(),
                    $actorUpdated->getAuthor(),
                    new String($this->cdbXml()),
                    new String($this->cdbXmlNamespaceUri())
                )
            ]
        ];
    }

    private function publish($payload)
    {
        $this->enricher->handle(
            DomainMessage::recordNow(
                UUID::generateAsString(),
                0,
                new Metadata(),
                $payload
            )
        );
    }

    private function newActorCreated(\DateTimeImmutable $time)
    {
        $actorId = new String('foo');
        $author = new String('me@example.com');

        return new ActorCreated(
            $actorId,
            $time,
            $author
        );
    }

    private function newActorUpdated(\DateTimeImmutable $time)
    {
        $actorId = new String('foo');
        $author = new String('me@example.com');

        return new ActorUpdated(
            $actorId,
            $time,
            $author
        );
    }

    /**
     * @dataProvider messagesProvider
     * @test
     * @param ActorUpdated|ActorCreated $incomingEvent
     * @param ActorUpdatedEnrichedWithCdbXml|ActorCreatedEnrichedWithCdbXml $newEvent
     */
    public function it_publishes_a_new_message_enriched_with_xml(
        $incomingEvent,
        $newEvent
    ) {
        $this->cdbXmlService->expects($this->once())
            ->method('getCdbXmlOfActor')
            ->with($incomingEvent->getActorId())
            ->willReturn(
                $this->cdbXml()
            );

        $this->publish($incomingEvent);

        $this->assertTracedEvents(
            [
                $newEvent
            ]
        );
    }

    /**
     *
     */
    public function messagesCausingOutdatedXmlExceptionProvider()
    {
        // Time is anything later than the lastupdated property in the xml file
        // with actor xml that is loaded.
        return [
            [
                $this->newActorUpdated(new \DateTimeImmutable())
            ],
            [
                $this->newActorCreated(new \DateTimeImmutable())
            ],
        ];
    }

    /**
     * @test
     * @dataProvider messagesCausingOutdatedXmlExceptionProvider
     * @param ActorUpdated|ActorCreated $event
     */
    public function it_fails_if_the_retrieved_xml_is_older_than_time_indicated_in_the_message($event)
    {
        $this->cdbXmlService->expects($this->once())
            ->method('getCdbXmlOfActor')
            ->with($event->getActorId())
            ->willReturn(
                $this->cdbXml()
            );

        $this->setExpectedException(OutdatedXmlRepresentationException::class);

        $this->publish($event);
    }

    /**
     * @param object[] $expectedEvents
     */
    protected function assertTracedEvents($expectedEvents)
    {
        $events = $this->eventBus->getEvents();

        $this->assertEquals(
            $expectedEvents,
            $events
        );
    }
}
