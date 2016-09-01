<?php

namespace CultuurNet\UDB3\Place;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Title;
use ValueObjects\Geography\Country;

class DefaultPlaceEditingServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DefaultPlaceEditingService
     */
    protected $placeEditingService;

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
     * @var TraceableEventStore
     */
    protected $eventStore;

    public function setUp()
    {
        $this->commandBus = $this->getMock(CommandBusInterface::class);

        $this->uuidGenerator = $this->getMock(
            UuidGeneratorInterface::class
        );

        $this->commandFactory = $this->getMock(OfferCommandFactoryInterface::class);

        /** @var DocumentRepositoryInterface $repository */
        $this->readRepository = $this->getMock(DocumentRepositoryInterface::class);

        $this->eventStore = new TraceableEventStore(
            new InMemoryEventStore()
        );
        $this->writeRepository = new PlaceRepository(
            $this->eventStore,
            new SimpleEventBus()
        );

        $this->placeEditingService = new DefaultPlaceEditingService(
            $this->commandBus,
            $this->uuidGenerator,
            $this->readRepository,
            $this->commandFactory,
            $this->writeRepository
        );
    }

    /**
     * @test
     */
    public function it_can_create_a_new_place()
    {
        $street = new Street('Kerkstraat 69');
        $locality = new Locality('Leuven');
        $postalCode = new PostalCode('3000');
        $country = Country::fromNative('BE');
        
        $placeId = 'generated-uuid';
        $title = new Title('Title');
        $eventType = new EventType('0.50.4.0.0', 'concert');
        $address = new Address($street, $postalCode, $locality, $country);
        $calendar = new Calendar('permanent', '', '');
        $theme = null;

        $this->uuidGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('generated-uuid');

        $this->eventStore->trace();

        $this->placeEditingService->createPlace($title, $eventType, $address, $calendar, $theme);

        $this->assertEquals(
            [
                new PlaceCreated(
                    $placeId,
                    $title,
                    $eventType,
                    $address,
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
    public function it_can_create_a_new_place_with_a_fixed_publication_date()
    {
        $publicationDate = \DateTimeImmutable::createFromFormat(\DateTime::ISO8601, '2016-08-01T00:00:00+0200');

        $street = new Street('Kerkstraat 69');
        $locality = new Locality('Leuven');
        $postalCode = new PostalCode('3000');
        $country = Country::fromNative('BE');
        $placeId = 'generated-uuid';
        $title = new Title('Title');
        $eventType = new EventType('0.50.4.0.0', 'concert');
        $address = new Address($street, $postalCode, $locality, $country);
        $calendar = new Calendar('permanent', '', '');
        $theme = null;

        $this->uuidGenerator->expects($this->once())
          ->method('generate')
          ->willReturn('generated-uuid');

        $this->eventStore->trace();

        $editingService = $this->placeEditingService->withFixedPublicationDateForNewOffers($publicationDate);

        $editingService->createPlace($title, $eventType, $address, $calendar, $theme);

        $this->assertEquals(
            [
                new PlaceCreated(
                    $placeId,
                    $title,
                    $eventType,
                    $address,
                    $calendar,
                    $theme,
                    $publicationDate
                )
            ],
            $this->eventStore->getEvents()
        );
    }
}
