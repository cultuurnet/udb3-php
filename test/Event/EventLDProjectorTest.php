<?php


namespace CultuurNet\UDB3\Event;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use Broadway\UuidGenerator\Testing\MockUuidGenerator;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Iri\CallableIriGenerator;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\PlaceService;
use CultuurNet\UDB3\Event\ReadModel\JSONLD\DescriptionFilterInterface;

class EventLDProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $documentRepository;

    /**
     * @var EventServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventService;

    /**
     * @var PlaceService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $placeService;

    /**
     * @var OrganizerService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $organizerService;

    /**
     * @var IriGeneratorInterface
     */
    private $iriGenerator;

    /**
     * @var EventLDProjector
     */
    private $projector;

    protected function setUp()
    {
        $this->documentRepository = $this->getMock(
            DocumentRepositoryInterface::class
        );

        $this->eventService = $this->getMock(
            EventServiceInterface::class
        );

        $this->placeService = $this->getMock(
            PlaceService::class,
            array(),
            array(),
            '',
            false
        );

        $this->organizerService = $this->getMock(
            OrganizerService::class,
            array(),
            array(),
            '',
            false
        );

        $this->iriGenerator = new CallableIriGenerator(
            function ($id) {
                return 'http://example.com/entity/' . $id;
            }
        );

        $this->projector = new EventLDProjector(
            $this->documentRepository,
            $this->iriGenerator,
            $this->eventService,
            $this->placeService,
            $this->organizerService
        );
    }


    /**
     * @test
     */
    public function it_handles_new_events()
    {
        $uuidGenerator = new Version4Generator();
        $eventId = $uuidGenerator->generate();
        $date = new \DateTime('2015-01-26T13:25:21+01:00');

        $eventCreated = new EventCreated(
            $eventId,
            new Title('some representative title'),
            'LOCATION-ABC-123',
            $date,
            new EventType('0.50.4.0.0', 'concert')
        );

        $jsonLD = new \stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $eventId;
        $jsonLD->{'@context'} = '/api/1.0/event.jsonld';
        $jsonLD->name = array('nl' => 'some representative title');
        $jsonLD->location = array(
            '@type' => 'Place',
            '@id' => 'http://example.com/entity/LOCATION-ABC-123'
        );
        $jsonLD->calendarType = 'single';
        $jsonLD->startDate = '2015-01-26T13:25:21+01:00';
        $jsonLD->sameAs = [
            'http://www.uitinvlaanderen.be/agenda/e/some-representative-title/' . $eventId,
        ];
        $jsonLD->terms = [
            [
                'label' => 'concert',
                'domain' => 'eventtype',
                'id' => '0.50.4.0.0',
            ]
        ];
        $jsonLD->created = '2015-01-20T13:25:21+01:00';

        $expectedDocument = (new JsonDocument($eventId))
            ->withBody($jsonLD);

        // Set up the placeService so that it does not know about the JSON-LD
        // representation of the Place yet and only returns the URI of the
        // Place.
        $this->placeService->expects($this->once())
            ->method('getEntity')
            ->with('LOCATION-ABC-123')
            ->willThrowException(new EntityNotFoundException());
        $this->placeService->expects($this->once())
            ->method('iri')
            ->willReturnCallback(
                function ($argument) {
                    return 'http://example.com/entity/' . $argument;
                }
            );

        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with($expectedDocument);

        $this->projector->handle(
            new DomainMessage(
                1,
                1,
                new Metadata(),
                $eventCreated,
                DateTime::fromString('2015-01-20T13:25:21+01:00')
            )
        );
    }

    /**
     * @test
     */
    public function it_strips_empty_keywords_when_importing_from_udb2()
    {
        $event = $this->eventImportedFromUDB2(
            'event_with_empty_keyword.cdbxml.xml'
        );

        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function ($jsonDocument) {
                        $expectedKeywords = ['gent', 'Quiz', 'Gent on Files'];
                        $body = $jsonDocument->getBody();
                        return count(
                            array_diff(
                                $expectedKeywords,
                                (array)$body->keywords
                            )
                        ) == 0;
                    }
                )
            );

        $this->projector->applyEventImportedFromUDB2($event);


    }

    private function eventImportedFromUDB2($fileName)
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/' . $fileName
        );
        $event = new EventImportedFromUDB2(
            'someId',
            $cdbXml,
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
        );

        return $event;
    }

    /**
     * @test
     */
    public function it_does_not_add_an_empty_keywords_property()
    {
        $event = $this->eventImportedFromUDB2(
            'event_without_keywords.cdbxml.xml'
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) {
                        $body = $jsonDocument->getBody();

                        return !property_exists($body, 'keywords');
                    }
                )
            );

        $this->projector->applyEventImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_does_not_add_an_empty_image_property()
    {
        $event = $this->eventImportedFromUDB2('event_without_image.cdbxml.xml');

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) {
                        $body = $jsonDocument->getBody();

                        return !property_exists($body, 'image');
                    }
                )
            );

        $this->projector->applyEventImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_adds_an_image_property_when_cdbxml_has_a_photo()
    {
        $event = $this->eventImportedFromUDB2('event_with_photo.cdbxml.xml');

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) {
                        $body = $jsonDocument->getBody();

                        return $body->image === '//media.uitdatabank.be/20141105/ed466c72-451f-4079-94d3-4ab2e0be7b15.jpg';
                    }
                )
            );

        $this->projector->applyEventImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_adds_a_bookingInfo_property_when_cdbxml_has_pricevalue()
    {
        $event = $this->eventImportedFromUDB2(
            'event_with_price_value.cdbxml.xml'
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) {
                        $body = $jsonDocument->getBody();
                        $bookingInfo = $body->bookingInfo;

                        $expectedBookingInfo = new \stdClass();
                        $expectedBookingInfo->currency = 'EUR';
                        $expectedBookingInfo->price = 0;

                        return
                            is_array($bookingInfo) &&
                            count($bookingInfo) === 1 &&
                            $bookingInfo[0] == $expectedBookingInfo;
                    }
                )
            );

        $this->projector->applyEventImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_adds_the_pricedescription_from_cdbxml_to_bookingInfo()
    {
        $event = $this->eventImportedFromUDB2(
            'event_with_price_value_and_description.cdbxml.xml'
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) {
                        $body = $jsonDocument->getBody();
                        $bookingInfo = $body->bookingInfo;

                        $expectedBookingInfo = new \stdClass();
                        $expectedBookingInfo->currency = 'EUR';
                        $expectedBookingInfo->price = 0;
                        $expectedBookingInfo->description = 'Gratis voor iedereen!';

                        return
                            is_array($bookingInfo) &&
                            count($bookingInfo) === 1 &&
                            $bookingInfo[0] == $expectedBookingInfo;
                    }
                )
            );

        $this->projector->applyEventImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_does_not_add_a_missing_price_from_cdbxml_to_bookingInfo()
    {
        $event = $this->eventImportedFromUDB2(
            'event_with_only_price_description.cdbxml.xml'
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) {
                        $body = $jsonDocument->getBody();
                        $bookingInfo = $body->bookingInfo;

                        $expectedBookingInfo = new \stdClass();
                        $expectedBookingInfo->description = 'Gratis voor iedereen!';

                        return
                            is_array($bookingInfo) &&
                            count($bookingInfo) === 1 &&
                            $bookingInfo[0] == $expectedBookingInfo;
                    }
                )
            );

        $this->projector->applyEventImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_does_not_add_booking_info_when_price_is_missing()
    {
        $event = $this->eventImportedFromUDB2(
            'event_without_price.cdbxml.xml'
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) {
                        $body = $jsonDocument->getBody();

                        return !property_exists($body, 'bookingInfo');
                    }
                )
            );

        $this->projector->applyEventImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_does_not_add_typical_age_range_when_age_from_is_missing()
    {
        $event = $this->eventImportedFromUDB2(
            'event_without_age_from.cdbxml.xml'
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) {
                        $body = $jsonDocument->getBody();

                        return !property_exists($body, 'typicalAgeRange');
                    }
                )
            );

        $this->projector->applyEventImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_adds_typical_age_range_when_age_from_is_present()
    {
        $event = $this->eventImportedFromUDB2(
            'event_with_age_from.cdbxml.xml'
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) {
                        $body = $jsonDocument->getBody();

                        return $body->typicalAgeRange === '10-';
                    }
                )
            );

        $this->projector->applyEventImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_adds_a_language_property_when_cdbxml_has_languages()
    {
        $event = $this->eventImportedFromUDB2(
            'event_with_languages.cdbxml.xml'
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) {
                        $body = $jsonDocument->getBody();

                        $languages = $body->language;
                        $expectedLanguages = [
                            'Nederlands',
                            'Frans',
                            'Engels'
                        ];

                        return is_array($languages) &&
                        $languages == $expectedLanguages;
                    }
                )
            );

        $this->projector->applyEventImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_does_not_add_an_empty_language_property()
    {
        $event = $this->eventImportedFromUDB2(
            'event_without_languages.cdbxml.xml'
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) {
                        $body = $jsonDocument->getBody();

                        return !property_exists($body, 'language');
                    }
                )
            );

        $this->projector->applyEventImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_filters_the_description_property_when_filters_are_added()
    {
        /** @var PlaceServiceInterface|\PHPUnit_Framework_MockObject_MockObject $filter */
        $filter = $this->getMock(DescriptionFilterInterface::class);
        $filter->expects($this->atLeastOnce())
            ->method('filter');

        $this->projector->addDescriptionFilter($filter);

        $event = $this->eventImportedFromUDB2(
            'event_without_languages.cdbxml.xml'
        );
        $this->projector->applyEventImportedFromUDB2($event);
    }
}
