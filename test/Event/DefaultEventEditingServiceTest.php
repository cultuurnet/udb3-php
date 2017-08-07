<?php

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Description;
use CultuurNet\UDB3\Event\Commands\UpdateAudience;
use CultuurNet\UDB3\Event\Commands\UpdateLocation;
use CultuurNet\UDB3\Event\Events\EventCopied;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ValueObjects\Audience;
use CultuurNet\UDB3\Event\ValueObjects\AudienceType;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\LabelServiceInterface;
use CultuurNet\UDB3\Language;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Location\LocationId;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;
use ValueObjects\Geography\Country;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use CultuurNet\UDB3\Title;

class DefaultEventEditingServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultEventEditingService
     */
    protected $eventEditingService;

    /**
     * @var EventServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventService;

    /**
     * @var CommandBusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandBus;

    /**
     * @var UuidGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uuidGenerator;

    /**
     * @var OfferCommandFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandFactory;

    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readRepository;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $writeRepository;

    /**
     * @var Label\LabelServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $labelService;

    /**
     * @var TraceableEventStore
     */
    protected $eventStore;

    public function setUp()
    {
        $this->eventService = $this->createMock(EventServiceInterface::class);

        $this->commandBus = $this->createMock(CommandBusInterface::class);

        $this->uuidGenerator = $this->createMock(UuidGeneratorInterface::class);

        $this->commandFactory = $this->createMock(OfferCommandFactoryInterface::class);

        /** @var DocumentRepositoryInterface $repository */
        $this->readRepository = $this->createMock(DocumentRepositoryInterface::class);

        $this->eventStore = new TraceableEventStore(
            new InMemoryEventStore()
        );

        $this->writeRepository = new EventRepository(
            $this->eventStore,
            new SimpleEventBus()
        );

        $this->labelService = $this->createMock(LabelServiceInterface::class);

        $this->eventEditingService = new DefaultEventEditingService(
            $this->eventService,
            $this->commandBus,
            $this->uuidGenerator,
            $this->readRepository,
            $this->commandFactory,
            $this->writeRepository,
            $this->labelService
        );
    }

    /**
     * @test
     */
    public function it_refuses_to_translate_title_of_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->expectException(DocumentGoneException::class);

        $this->setUpEventNotFound($id);

        $this->eventEditingService->translateTitle(
            $id,
            new Language('nl'),
            new StringLiteral('new title')
        );
    }

    /**
     * @test
     */
    public function it_refuses_to_update_the_description_of_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->expectException(DocumentGoneException::class);

        $this->setUpEventNotFound($id);

        $this->eventEditingService->updateDescription(
            $id,
            new Language('en'),
            new Description('new description')
        );
    }

    /**
     * @test
     */
    public function it_refuses_to_label_an_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->expectException(DocumentGoneException::class);

        $this->setUpEventNotFound($id);

        $this->eventEditingService->addLabel($id, new Label('foo'));
    }

    /**
     * @test
     */
    public function it_refuses_to_remove_a_label_from_an_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->expectException(DocumentGoneException::class);

        $this->setUpEventNotFound($id);

        $this->eventEditingService->RemoveLabel($id, new Label('foo'));
    }

    /**
     * @test
     */
    public function it_can_create_a_new_event()
    {
        $eventId = 'generated-uuid';
        $title = new Title('Title');
        $eventType = new EventType('0.50.4.0.0', 'concert');
        $street = new Street('Kerkstraat 69');
        $locality = new Locality('Leuven');
        $postalCode = new PostalCode('3000');
        $country = Country::fromNative('BE');
        $address = new Address($street, $postalCode, $locality, $country);
        $location = new Location(UUID::generateAsString(), new StringLiteral('P-P-Partyzone'), $address);
        $calendar = new Calendar(CalendarType::PERMANENT());
        $theme = null;

        $this->eventStore->trace();

        $this->uuidGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('generated-uuid');

        $this->eventEditingService->createEvent($title, $eventType, $location, $calendar, $theme);

        $this->assertEquals(
            [
                new EventCreated(
                    $eventId,
                    $title,
                    $eventType,
                    $location,
                    $calendar,
                    $theme
                ),
            ],
            $this->eventStore->getEvents()
        );
    }

    /**
     * @test
     */
    public function it_can_copy_an_existing_event()
    {
        $eventId = 'e49430ca-5729-4768-8364-02ddb385517a';
        $originalEventId = '27105ae2-7e1c-425e-8266-4cb86a546159';
        $calendar = new Calendar(CalendarType::PERMANENT());

        $title = new Title('Title');
        $eventType = new EventType('0.50.4.0.0', 'concert');
        $location = new Location(
            UUID::generateAsString(),
            new StringLiteral('Het Depot'),
            new Address(
                new Street('Martelarenplein'),
                new PostalCode('3000'),
                new Locality('Leuven'),
                Country::fromNative('BE')
            )
        );
        $theme = null;

        $this->eventStore->trace();

        $this->uuidGenerator->expects($this->exactly(2))
            ->method('generate')
            ->willReturnOnConsecutiveCalls($originalEventId, $eventId);

        $this->eventEditingService->createEvent(
            $title,
            $eventType,
            $location,
            $calendar,
            $theme
        );

        $this->eventEditingService->copyEvent($originalEventId, $calendar);

        $this->assertEquals(
            [
                new EventCreated(
                    $originalEventId,
                    $title,
                    $eventType,
                    $location,
                    $calendar,
                    $theme
                ),
                new EventCopied(
                    $eventId,
                    $originalEventId,
                    $calendar
                ),
            ],
            $this->eventStore->getEvents()
        );
    }

    /**
     * @test
     */
    public function it_throws_an_invalid_argument_exception_during_copy_when_type_mismatch_for_original_event_id()
    {
        $originalEventId = '27105ae2-7e1c-425e-8266-4cb86a546159';
        $calendar = new Calendar(CalendarType::PERMANENT());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'No original event found to copy with id ' . $originalEventId
        );

        $this->eventEditingService->copyEvent($originalEventId, $calendar);
    }

    /**
     * @test
     */
    public function it_throws_an_invalid_argument_exception_during_copy_when_original_event_is_missing()
    {
        $originalEventId = false;
        $calendar = new Calendar(CalendarType::PERMANENT());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Expected originalEventId to be a string, received bool'
        );

        $this->eventEditingService->copyEvent($originalEventId, $calendar);
    }

    /**
     * @test
     */
    public function it_can_create_a_new_event_with_a_fixed_publication_date()
    {
        $eventId = 'generated-uuid';
        $title = new Title('Title');
        $eventType = new EventType('0.50.4.0.0', 'concert');
        $street = new Street('Kerkstraat 69');
        $locality = new Locality('Leuven');
        $postalCode = new PostalCode('3000');
        $country = Country::fromNative('BE');
        $address = new Address($street, $postalCode, $locality, $country);
        $location = new Location(UUID::generateAsString(), new StringLiteral('P-P-Partyzone'), $address);
        $calendar = new Calendar(CalendarType::PERMANENT());
        $theme = null;
        $publicationDate = \DateTimeImmutable::createFromFormat(
            \DateTime::ATOM,
            '2016-08-01T00:00:00+00:00'
        );

        $this->eventEditingService = $this->eventEditingService
            ->withFixedPublicationDateForNewOffers($publicationDate);

        $this->eventStore->trace();

        $this->uuidGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('generated-uuid');

        $this->eventEditingService->createEvent($title, $eventType, $location, $calendar, $theme);

        $this->assertEquals(
            [
                new EventCreated(
                    $eventId,
                    $title,
                    $eventType,
                    $location,
                    $calendar,
                    $theme,
                    $publicationDate
                ),
            ],
            $this->eventStore->getEvents()
        );
    }

    /**
     * @test
     */
    public function it_can_dispatch_an_update_audience_command()
    {
        $eventId = 'd2b41f1d-598c-46af-a3a5-10e373faa6fe';

        $audience = new Audience(AudienceType::EDUCATION());

        $expectedCommandId = 'commandId';

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with(new UpdateAudience($eventId, $audience))
            ->willReturn($expectedCommandId);

        $commandId = $this->eventEditingService->updateAudience($eventId, $audience);

        $this->assertEquals($expectedCommandId, $commandId);
    }

    /**
     * @test
     */
    public function it_can_dispatch_an_update_location_command()
    {
        $eventId = '3ed90f18-93a3-4340-981d-12e57efa0211';

        $locationId = new LocationId('57738178-28a5-4afb-90c0-fd0beba172a8');

        $updateLocation = new UpdateLocation($eventId, $locationId);

        $expectedCommandId = 'commandId';

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with($updateLocation)
            ->willReturn($expectedCommandId);

        $commandId = $this->eventEditingService->updateLocation($eventId, $locationId);

        $this->assertEquals($expectedCommandId, $commandId);
    }

    /**
     * @param mixed $id
     */
    private function setUpEventNotFound($id)
    {
        $this->readRepository->expects($this->once())
            ->method('get')
            ->with($id)
            ->willThrowException(new DocumentGoneException());
    }
}
