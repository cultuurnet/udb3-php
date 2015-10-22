<?php


namespace CultuurNet\UDB3\Event;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventWasLabelled;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\Events\Unlabelled;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\EventXmlString;
use CultuurNet\UDB3\Iri\CallableIriGenerator;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Organizer\OrganizerProjectedToJSONLD;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\Place\PlaceProjectedToJSONLD;
use CultuurNet\UDB3\PlaceService;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\StringFilter\StringFilterInterface;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use PHPUnit_Framework_MockObject_MockObject;
use stdClass;
use ValueObjects\String\String;

class EventLDProjectorTest extends CdbXMLProjectorTestBase
{

    use \CultuurNet\UDB3\OfferLDProjectorTestTrait;

    const CDBXML_NAMESPACE = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL';

    /**
     * @var DocumentRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $documentRepository;

    /**
     * @var EventServiceInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $eventService;

    /**
     * @var PlaceService|PHPUnit_Framework_MockObject_MockObject
     */
    private $placeService;

    /**
     * @var OrganizerService|PHPUnit_Framework_MockObject_MockObject
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
    public function it_handles_new_events_without_theme()
    {
        $uuidGenerator = new Version4Generator();
        $eventId = $uuidGenerator->generate();
        $date = new \DateTime('2015-01-26T13:25:21+01:00');

        $eventCreated = new EventCreated(
            $eventId,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Location('LOCATION-ABC-123', '$name', '$country', '$locality', '$postalcode', '$street'),
            new Calendar('single', '2015-01-26T13:25:21+01:00')
        );

        $jsonLD = new stdClass();
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
                'id' => '0.50.4.0.0',
                'label' => 'concert',
                'domain' => 'eventtype',
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
    public function it_handles_new_events_with_theme()
    {
        $uuidGenerator = new Version4Generator();
        $eventId = $uuidGenerator->generate();
        $date = new \DateTime('2015-01-26T13:25:21+01:00');

        $eventCreated = new EventCreated(
            $eventId,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Location('LOCATION-ABC-123', '$name', '$country', '$locality', '$postalcode', '$street'),
            new Calendar('single', '2015-01-26T13:25:21+01:00'),
            new Theme('123', 'theme label')
        );

        $jsonLD = new stdClass();
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
                'id' => '0.50.4.0.0',
                'label' => 'concert',
                'domain' => 'eventtype',
            ],
            [
                'id' => '123',
                'label' => 'theme label',
                'domain' => 'theme',
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
    public function it_handles_new_events_with_creator()
    {
        $uuidGenerator = new Version4Generator();
        $eventId = $uuidGenerator->generate();
        $date = new \DateTime('2015-01-26T13:25:21+01:00');

        $eventCreated = new EventCreated(
            $eventId,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Location('LOCATION-ABC-123', '$name', '$country', '$locality', '$postalcode', '$street'),
            new Calendar('single', '2015-01-26T13:25:21+01:00'),
            new Theme('123', 'theme label')
        );

        $jsonLD = new stdClass();
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
                'id' => '0.50.4.0.0',
                'label' => 'concert',
                'domain' => 'eventtype',
            ],
            [
                'id' => '123',
                'label' => 'theme label',
                'domain' => 'theme',
            ]
        ];
        $jsonLD->created = '2015-01-20T13:25:21+01:00';
        $jsonLD->creator = '1 (Tester)';

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

        $metadata = array(
          'user_id' => '1',
          'user_nick' => 'Tester'
        );
        $this->projector->handle(
            new DomainMessage(
                1,
                1,
                new Metadata($metadata),
                $eventCreated,
                DateTime::fromString('2015-01-20T13:25:21+01:00')
            )
        );
    }

    /**
     * @test
     */
    public function it_handles_new_events_with_multiple_timestamps()
    {
        $uuidGenerator = new Version4Generator();
        $eventId = $uuidGenerator->generate();
        $date = new \DateTime('2015-01-26T13:25:21+01:00');

        $timestamps = [
            new \CultuurNet\UDB3\Timestamp('2015-01-26T13:25:21+01:00', '2015-01-27T13:25:21+01:00'),
            new \CultuurNet\UDB3\Timestamp('2015-01-28T13:25:21+01:00', '2015-01-29T13:25:21+01:00')
        ];
        $eventCreated = new EventCreated(
            $eventId,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Location('LOCATION-ABC-123', '$name', '$country', '$locality', '$postalcode', '$street'),
            new Calendar('multiple', '2015-01-26T13:25:21+01:00', '2015-01-29T13:25:21+01:00', $timestamps),
            new Theme('123', 'theme label')
        );

        $jsonLD = new stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $eventId;
        $jsonLD->{'@context'} = '/api/1.0/event.jsonld';
        $jsonLD->name = array('nl' => 'some representative title');
        $jsonLD->location = array(
            '@type' => 'Place',
            '@id' => 'http://example.com/entity/LOCATION-ABC-123'
        );
        $jsonLD->calendarType = 'multiple';
        $jsonLD->startDate = '2015-01-26T13:25:21+01:00';
        $jsonLD->endDate = '2015-01-29T13:25:21+01:00';
        $jsonLD->subEvent = array();
        $jsonLD->subEvent[] = array(
            '@type' => 'Event',
            'startDate' => '2015-01-26T13:25:21+01:00',
            'endDate' => '2015-01-27T13:25:21+01:00',
        );
        $jsonLD->subEvent[] = array(
            '@type' => 'Event',
            'startDate' => '2015-01-28T13:25:21+01:00',
            'endDate' => '2015-01-29T13:25:21+01:00',
        );
        $jsonLD->sameAs = [
            'http://www.uitinvlaanderen.be/agenda/e/some-representative-title/' . $eventId,
        ];
        $jsonLD->terms = [
            [
                'id' => '0.50.4.0.0',
                'label' => 'concert',
                'domain' => 'eventtype',
            ],
            [
                'id' => '123',
                'label' => 'theme label',
                'domain' => 'theme',
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
            'samples/event_with_empty_keyword.cdbxml.xml'
        );

        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) {
                        $expectedLabels = ['gent', 'Quiz', 'Gent on Files'];
                        $body = $jsonDocument->getBody();
                        return count(
                            array_diff(
                                $expectedLabels,
                                (array)$body->labels
                            )
                        ) == 0;
                    }
                )
            );

        $this->projector->applyEventImportedFromUDB2($event);


    }

    /**
     * @test
     */
    public function it_does_not_add_an_empty_labels_property()
    {
        $event = $this->eventImportedFromUDB2(
            'samples/event_without_keywords.cdbxml.xml'
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) {
                        $body = $jsonDocument->getBody();

                        return !property_exists($body, 'labels');
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
        $event = $this->eventImportedFromUDB2('samples/event_without_image.cdbxml.xml');

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
        $event = $this->eventImportedFromUDB2('samples/event_with_photo.cdbxml.xml');

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
            'samples/event_with_price_value.cdbxml.xml'
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
                        $expectedBookingInfo->priceCurrency = 'EUR';
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
            'samples/event_with_price_value_and_description.cdbxml.xml'
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
                        $expectedBookingInfo->priceCurrency = 'EUR';
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
            'samples/event_with_only_price_description.cdbxml.xml'
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) {
                        $body = $jsonDocument->getBody();
                        $bookingInfo = $body->bookingInfo;

                        $expectedBookingInfo = new stdClass();
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
            'samples/event_without_price.cdbxml.xml'
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
            'samples/event_without_age_from.cdbxml.xml'
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
            'samples/event_with_age_from.cdbxml.xml'
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
            'samples/event_with_languages.cdbxml.xml'
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
            'samples/event_without_languages.cdbxml.xml'
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
        /** @var StringFilterInterface|PHPUnit_Framework_MockObject_MockObject $filter */
        $filter = $this->getMock(StringFilterInterface::class);
        $filter->expects($this->atLeastOnce())
            ->method('filter');

        $this->projector->addDescriptionFilter($filter);

        $event = $this->eventImportedFromUDB2(
            'samples/event_without_languages.cdbxml.xml'
        );
        $this->projector->applyEventImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_projects_the_addition_of_a_label()
    {
        $eventWasLabelled = new EventWasLabelled(
            'foo',
            new Label('label B')
        );

        $initialDocument = new JsonDocument(
            'foo',
            json_encode([
                'labels' => ['label A']
            ])
        );

        $expectedDocument = new JsonDocument(
            'foo',
            json_encode([
                'labels' => ['label A', 'label B']
            ])
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('get')
            ->with('foo')
            ->willReturn($initialDocument);

        $this->documentRepository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedDocument) {
                    return $expectedDocument == $jsonDocument;
                }
            ));

        $this->projector->applyEventWasLabelled($eventWasLabelled);
    }

    /**
     * @test
     */
    public function it_projects_the_removal_of_a_label()
    {
        $initialDocument = new JsonDocument(
            'foo',
            json_encode([
                'labels' => ['label A', 'label B', 'label C']
            ])
        );

        $eventWasUnlabelled = new Unlabelled(
            'foo',
            new Label('label B')
        );

        $expectedDocument = new JsonDocument(
            'foo',
            json_encode([
                'labels' => ['label A', 'label C']
            ])
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('get')
            ->with('foo')
            ->willReturn($initialDocument);

        $this->documentRepository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedDocument) {
                    return $expectedDocument == $jsonDocument;
                }
            ));

        $this->projector->applyUnlabelled($eventWasUnlabelled);
    }

    /**
     * @test
     */
    public function it_projects_the_addition_of_a_label_to_an_event_without_existing_labels()
    {
        $eventWasLabelled = new EventWasLabelled(
            'foo',
            new Label('label B')
        );

        $initialDocument = new JsonDocument(
            'foo',
            json_encode([
                'bar' => 'stool'
            ])
        );

        $expectedDocument = new JsonDocument(
            'foo',
            json_encode([
                'bar' => 'stool',
                'labels' => ['label B']
            ])
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('get')
            ->with('foo')
            ->willReturn($initialDocument);

        $this->documentRepository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedDocument) {
                    return $expectedDocument == $jsonDocument;
                }
            ));

        $this->projector->applyEventWasLabelled($eventWasLabelled);
    }

    /**
     * @test
     */
    public function it_embeds_the_projection_of_a_place_in_all_events_located_at_that_place()
    {
        $eventID = '468';
        $secondEventID = '579';

        $placeID = '101214';

        $this->eventService
            ->expects($this->once())
            ->method('eventsLocatedAtPlace')
            ->with($placeID)
            ->willReturn(
                [
                    $eventID,
                    $secondEventID,
                ]
            );

        $placeJSONLD = json_encode(
            [
                'name' => "t,arsenaal mechelen",
                'address' => [
                    'addressCountry' => "BE",
                    'addressLocality' => "Mechelen",
                    'postalCode' => "2800",
                    'streetAddress' => "Hanswijkstraat 63",
                ],
            ]
        );

        $this->placeService
            ->expects($this->once())
            ->method('getEntity')
            ->with($placeID)
            ->willReturn($placeJSONLD);

        $initialEventDocument = new JsonDocument(
            $eventID,
            json_encode([
              'labels' => ['test 1', 'test 2'],
            ])
        );

        $initialSecondEventDocument = new JsonDocument(
            $secondEventID,
            json_encode([
                'name' => [
                    'nl' => 'Quicksand Valley',
                ],
            ])
        );

        $this->documentRepository
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [$eventID],
                [$secondEventID]
            )
            ->willReturnOnConsecutiveCalls(
                $initialEventDocument,
                $initialSecondEventDocument
            );

        $expectedEventDocument = $initialEventDocument->withBody(
            (object)[
                'labels' => ['test 1', 'test 2'],
                'location' => [
                    'name' => "t,arsenaal mechelen",
                    'address' => [
                        'addressCountry' => "BE",
                        'addressLocality' => "Mechelen",
                        'postalCode' => "2800",
                        'streetAddress' => "Hanswijkstraat 63",
                    ],
                ],
            ]
        );

        $expectedSecondEventDocument = $initialSecondEventDocument->withBody(
            (object) [
                'name' => [
                    'nl' => 'Quicksand Valley',
                ],
                'location' => [
                    'name' => "t,arsenaal mechelen",
                    'address' => [
                        'addressCountry' => "BE",
                        'addressLocality' => "Mechelen",
                        'postalCode' => "2800",
                        'streetAddress' => "Hanswijkstraat 63",
                    ],
                ],
            ]
        );

        $this->documentRepository
            ->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive(
                [$expectedEventDocument],
                [$expectedSecondEventDocument]
            );

        $placeProjectedToJSONLD = new PlaceProjectedToJSONLD($placeID);

        $this->projector->handle(
            DomainMessage::recordNow(
                $placeProjectedToJSONLD->getId(),
                0,
                new Metadata(),
                $placeProjectedToJSONLD
            )
        );
    }/**
     * @test
     */
    public function it_embeds_the_projection_of_an_organizer_in_all_related_events()
    {
        $eventID = '468';
        $secondEventID = '579';

        $organizerId = '101214';

        $this->eventService
            ->expects($this->once())
            ->method('eventsOrganizedByOrganizer')
            ->with($organizerId)
            ->willReturn(
                [
                    $eventID,
                    $secondEventID,
                ]
            );

        $organizerJSONLD = json_encode(
            [
                'name' => 'stichting tegen Kanker',
                'email' => [
                    'kgielens@stichtingtegenkanker.be',
                ],
            ]
        );

        $this->organizerService
            ->expects($this->once())
            ->method('getEntity')
            ->with($organizerId)
            ->willReturn($organizerJSONLD);

        $initialEventDocument = new JsonDocument(
            $eventID,
            json_encode([
              'labels' => ['beweging', 'kanker'],
            ])
        );

        $initialSecondEventDocument = new JsonDocument(
            $secondEventID,
            json_encode([
                'name' => [
                    'nl' => 'Rekanto - TaiQi',
                    'fr' => 'Raviva - TaiQi'
                ],
            ])
        );

        $this->documentRepository
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [$eventID],
                [$secondEventID]
            )
            ->willReturnOnConsecutiveCalls(
                $initialEventDocument,
                $initialSecondEventDocument
            );

        $expectedEventDocument = $initialEventDocument->withBody(
            (object)[
                'labels' => ['beweging', 'kanker'],
                'organizer' => [
                    'name' => 'stichting tegen Kanker',
                    'email' => [
                        'kgielens@stichtingtegenkanker.be',
                    ],
                ],
            ]
        );

        $expectedSecondEventDocument = $initialSecondEventDocument->withBody(
            (object) [
                'name' => [
                    'nl' => 'Rekanto - TaiQi',
                    'fr' => 'Raviva - TaiQi'
                ],
                'organizer' => [
                    'name' => 'stichting tegen Kanker',
                    'email' => [
                        'kgielens@stichtingtegenkanker.be',
                    ],
                ],
            ]
        );

        $this->documentRepository
            ->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive(
                [$expectedEventDocument],
                [$expectedSecondEventDocument]
            );

        $organizerProjectedToJSONLD = new OrganizerProjectedToJSONLD($organizerId);

        $this->projector->handle(
            DomainMessage::recordNow(
                $organizerProjectedToJSONLD->getId(),
                0,
                new Metadata(),
                $organizerProjectedToJSONLD
            )
        );
    }

    /**
     * @test
     */
    public function it_projects_the_updating_of_major_info()
    {

        // Make sure the places entities return an iri.
        $this->placeService->expects($this->once())
            ->method('getEntity')
            ->with('LOCATION-ABC-456')
            ->willThrowException(new EntityNotFoundException());
        $this->placeService->expects($this->once())
            ->method('iri')
            ->willReturnCallback(
                function ($argument) {
                    return 'http://example.com/entity/' . $argument;
                }
            );

        $id = 'foo';
        $title = new Title('new title');
        $eventType = new EventType('0.50.4.0.1', 'concertnew');
        $location = new Location('LOCATION-ABC-456', '$newName', '$newCountry', '$newLocality', '$newPostalcode', '$newStreet');
        $calendar = new Calendar('single', '2015-01-26T13:25:21+01:00', '2015-02-26T13:25:21+01:00');
        $theme = new Theme('123', 'theme label');
        $majorInfoUpdated = new MajorInfoUpdated($id, $title, $eventType, $location, $calendar, $theme);

        $jsonLD = new stdClass();
        $jsonLD->id = $id;
        $jsonLD->name = ['nl' => 'some representative title'];
        $jsonLD->location = [
            '@type' => 'Place',
            '@id' => 'http://example.com/entity/LOCATION-ABC-123'
        ];
        $jsonLD->calendarType = 'permanent';
        $jsonLD->terms = [
            [
                'id' => '0.50.4.0.0',
                'label' => 'concert',
                'domain' => 'eventtype',
            ]
        ];

        $initialDocument = (new JsonDocument('foo'))
            ->withBody($jsonLD);

        $expectedJsonLD = new stdClass();
        $expectedJsonLD->id = $id;
        $expectedJsonLD->name = ['nl' => 'new title'];
        $expectedJsonLD->location = [
            '@type' => 'Place',
            '@id' => 'http://example.com/entity/LOCATION-ABC-456'
        ];
        $expectedJsonLD->calendarType = 'single';
        $expectedJsonLD->terms = [
            [
                'id' => '0.50.4.0.1',
                'label' => 'concertnew',
                'domain' => 'eventtype',
            ],
            [
                'id' => '123',
                'label' => 'theme label',
                'domain' => 'theme',
            ]
        ];
        $expectedJsonLD->startDate = '2015-01-26T13:25:21+01:00';
        $expectedJsonLD->endDate = '2015-02-26T13:25:21+01:00';

        $expectedDocument = (new JsonDocument('foo'))
            ->withBody($expectedJsonLD);

        $this->documentRepository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($initialDocument);

        $this->documentRepository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedDocument) {
                    return $expectedDocument == $jsonDocument;
                }
            ));

        $this->projector->applyMajorInfoUpdated($majorInfoUpdated);

    }

    /**
     * @test
     */
    public function it_deletes_events()
    {

        $id = 'foo';
        $this->documentRepository->expects($this->once())
            ->method('remove')
            ->with($id);

        $eventDeleted = new EventDeleted($id);
        $this->projector->applyEventDeleted($eventDeleted);

    }

    /**
     * @test
     */
    public function it_creates_events_from_cdbxml()
    {
        $xml = file_get_contents(__DIR__ . '/ReadModel/JSONLD/event_entryapi_valid.xml');

        $eventCreatedFromCdbXml = new EventCreatedFromCdbXml(
            new String('foo'),
            new EventXmlString($xml),
            new String(self::CDBXML_NAMESPACE)
        );

        $importedDate = '2015-03-01T10:17:19.176169+02:00';

        $metadata = array();
        $metadata['user_nick'] = 'Jantest';
        $metadata['consumer']['name'] = 'UiTDatabank';

        $domainMessage = new DomainMessage(
            $eventCreatedFromCdbXml->getEventId()->toNative(),
            1,
            new Metadata($metadata),
            $eventCreatedFromCdbXml,
            DateTime::fromString($importedDate)
        );

        $expectedJsonLD = file_get_contents(__DIR__ . '/ReadModel/JSONLD/event_entryapi_valid_expected.json');

        $expectedDocument = (new JsonDocument('foo', $expectedJsonLD));

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) use ($expectedDocument) {
                        return $expectedDocument == $jsonDocument;
                    }
                )
            );

        $this->projector->handle($domainMessage);
    }

    /**
     * @test
     */
    public function it_updates_Events_from_cdbxml()
    {
        $xml = file_get_contents(__DIR__ . '/ReadModel/JSONLD/event_entryapi_valid.xml');

        $eventUpdatedFromCdbXml = new EventUpdatedFromCdbXml(
            new String('foo'),
            new EventXmlString($xml),
            new String(self::CDBXML_NAMESPACE)
        );

        $importedDate = '2015-03-01T10:17:19.176169+02:00';

        $metadata = array();
        $metadata['user_nick'] = 'Jantest';
        $metadata['consumer']['name'] = 'UiTDatabank';

        $domainMessage = new DomainMessage(
            $eventUpdatedFromCdbXml->getEventId()->toNative(),
            1,
            new Metadata($metadata),
            $eventUpdatedFromCdbXml,
            DateTime::fromString($importedDate)
        );

        $expectedJsonLD = file_get_contents(__DIR__ . '/ReadModel/JSONLD/event_entryapi_valid_expected.json');

        $expectedDocument = (new JsonDocument('foo', $expectedJsonLD));

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) use ($expectedDocument) {
                        return $expectedDocument == $jsonDocument;
                    }
                )
            );

        $this->projector->handle($domainMessage);
    }
}
