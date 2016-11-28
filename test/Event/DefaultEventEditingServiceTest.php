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
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\EventNotFoundException;
use CultuurNet\UDB3\Event\EventServiceInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\LabelServiceInterface;
use CultuurNet\UDB3\Language;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;
use CultuurNet\UDB3\PlaceService;
use ValueObjects\Geography\Country;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;
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
        $this->eventService = $this->getMock(EventServiceInterface::class);

        $this->commandBus = $this->getMock(CommandBusInterface::class);

        $this->uuidGenerator = $this->getMock(
            UuidGeneratorInterface::class
        );

        $this->commandFactory = $this->getMock(OfferCommandFactoryInterface::class);

        /** @var DocumentRepositoryInterface $repository */
        $this->readRepository = $this->getMock(DocumentRepositoryInterface::class);
        /** @var PlaceService $placeService */
        $placeService = $this->getMock(
            PlaceService::class,
            array(),
            array(),
            '',
            false
        );

        $this->eventStore = new TraceableEventStore(
            new InMemoryEventStore()
        );

        $this->writeRepository = new EventRepository(
            $this->eventStore,
            new SimpleEventBus()
        );

        $this->labelService = $this->getMock(LabelServiceInterface::class);

        $this->eventEditingService = new DefaultEventEditingService(
            $this->eventService,
            $this->commandBus,
            $this->uuidGenerator,
            $this->readRepository,
            $placeService,
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

        $this->setExpectedException(DocumentGoneException::class);

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
    public function it_refuses_to_translate_description_of_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException(DocumentGoneException::class);

        $this->setUpEventNotFound($id);

        $this->eventEditingService->translateDescription(
            $id,
            new Language('nl'),
            new StringLiteral('new description')
        );
    }

    /**
     * @test
     */
    public function it_refuses_to_label_an_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException(DocumentGoneException::class);

        $this->setUpEventNotFound($id);

        $this->eventEditingService->addLabel($id, new Label('foo'));
    }

    /**
     * @test
     */
    public function it_refuses_to_remove_a_label_from_an_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException(DocumentGoneException::class);

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
                )
            ],
            $this->eventStore->getEvents()
        );
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
                )
            ],
            $this->eventStore->getEvents()
        );
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
