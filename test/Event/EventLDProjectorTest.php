<?php

namespace CultuurNet\UDB3\Event;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventWasLabelled;
use CultuurNet\UDB3\Event\Events\ImageAdded;
use CultuurNet\UDB3\Event\Events\LabelsMerged;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\Events\TranslationApplied;
use CultuurNet\UDB3\Event\Events\TranslationDeleted;
use CultuurNet\UDB3\Event\Events\Unlabelled;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\EventXmlString;
use CultuurNet\UDB3\Iri\CallableIriGenerator;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Media\MediaObject;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use CultuurNet\UDB3\Media\Serialization\MediaObjectSerializer;
use CultuurNet\UDB3\OfferLDProjectorTestBase;
use CultuurNet\UDB3\Organizer\OrganizerProjectedToJSONLD;
use CultuurNet\UDB3\Place\PlaceProjectedToJSONLD;
use CultuurNet\UDB3\PlaceService;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\StringFilter\StringFilterInterface;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Timestamp;
use CultuurNet\UDB3\Title;
use PHPUnit_Framework_MockObject_MockObject;
use stdClass;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;
use ValueObjects\Web\Url;

class EventLDProjectorTest extends OfferLDProjectorTestBase
{
    const CDBXML_NAMESPACE = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL';

    /**
     * @var EventServiceInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $eventService;

    /**
     * @var PlaceService|PHPUnit_Framework_MockObject_MockObject
     */
    private $placeService;

    /**
     * @var IriGeneratorInterface
     */
    private $iriGenerator;

    /**
     * @var CdbXMLEventFactory
     */
    private $cdbXMLEventFactory;

    /**
     * @var EventLDProjector
     */
    protected $projector;

    /**
     * @var Serializer|PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializer;

    /**
     * Constructs a test case with the given name.
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName, 'CultuurNet\\UDB3\\Event');
    }

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->cdbXMLEventFactory = new CdbXMLEventFactory();

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

        $this->iriGenerator = new CallableIriGenerator(
            function ($id) {
                return 'http://example.com/entity/' . $id;
            }
        );

        $this->serializer = new MediaObjectSerializer($this->iriGenerator);

        $this->projector = new EventLDProjector(
            $this->documentRepository,
            $this->iriGenerator,
            $this->eventService,
            $this->placeService,
            $this->organizerService,
            $this->serializer
        );
    }

    /**
     * @test
     */
    public function it_handles_new_events_without_theme()
    {
        $eventId = '1';

        $eventCreated = new EventCreated(
            $eventId,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Location('LOCATION-ABC-123', '$name', '$country', '$locality', '$postalcode', '$street'),
            new Calendar('single', '2015-01-26T13:25:21+01:00')
        );

        $jsonLD = new stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/1';
        $jsonLD->{'@context'} = '/api/1.0/event.jsonld';
        $jsonLD->name = (object)[
            'nl' => 'some representative title'
        ];
        $jsonLD->location = (object)[
            '@type' => 'Place',
            '@id' => 'http://example.com/entity/LOCATION-ABC-123'
        ];
        $jsonLD->calendarType = 'single';
        $jsonLD->startDate = '2015-01-26T13:25:21+01:00';
        $jsonLD->sameAs = [
            'http://www.uitinvlaanderen.be/agenda/e/some-representative-title/1',
        ];
        $jsonLD->terms = [
            (object)[
                'id' => '0.50.4.0.0',
                'label' => 'concert',
                'domain' => 'eventtype',
            ]
        ];
        $jsonLD->created = '2015-01-20T13:25:21+01:00';
        $jsonLD->modified = '2015-01-20T13:25:21+01:00';

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

        $body = $this->project(
            $eventCreated,
            $eventId,
            new Metadata(),
            DateTime::fromString('2015-01-20T13:25:21+01:00')
        );

        $this->assertEquals($jsonLD, $body);
    }

    /**
     * @test
     */
    public function it_handles_new_events_with_theme()
    {
        $eventId = '1';

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
        $jsonLD->name = (object)[
            'nl' => 'some representative title'
        ];
        $jsonLD->location = (object)[
            '@type' => 'Place',
            '@id' => 'http://example.com/entity/LOCATION-ABC-123'
        ];
        $jsonLD->calendarType = 'single';
        $jsonLD->startDate = '2015-01-26T13:25:21+01:00';
        $jsonLD->sameAs = [
            'http://www.uitinvlaanderen.be/agenda/e/some-representative-title/' . $eventId,
        ];
        $jsonLD->terms = [
            (object)[
                'id' => '0.50.4.0.0',
                'label' => 'concert',
                'domain' => 'eventtype',
            ],
            (object)[
                'id' => '123',
                'label' => 'theme label',
                'domain' => 'theme',
            ]
        ];
        $jsonLD->created = '2015-01-20T13:25:21+01:00';
        $jsonLD->modified = '2015-01-20T13:25:21+01:00';

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

        $body = $this->project(
            $eventCreated,
            $eventId,
            null,
            DateTime::fromString('2015-01-20T13:25:21+01:00')
        );

        $this->assertEquals(
            $jsonLD,
            $body
        );
    }

    /**
     * @test
     * @dataProvider eventCreatorDataProvider
     *
     * @param Metadata $metadata
     * @param string $expectedCreator
     */
    public function it_handles_new_events_with_creator(Metadata $metadata, $expectedCreator)
    {
        $eventId = '1';

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
        $jsonLD->name = (object)[
            'nl' => 'some representative title'
        ];
        $jsonLD->location = (object)[
            '@type' => 'Place',
            '@id' => 'http://example.com/entity/LOCATION-ABC-123'
        ];
        $jsonLD->calendarType = 'single';
        $jsonLD->startDate = '2015-01-26T13:25:21+01:00';
        $jsonLD->sameAs = [
            'http://www.uitinvlaanderen.be/agenda/e/some-representative-title/' . $eventId,
        ];
        $jsonLD->terms = [
            (object)[
                'id' => '0.50.4.0.0',
                'label' => 'concert',
                'domain' => 'eventtype',
            ],
            (object)[
                'id' => '123',
                'label' => 'theme label',
                'domain' => 'theme',
            ]
        ];
        $jsonLD->created = '2015-01-20T13:25:21+01:00';
        $jsonLD->modified = '2015-01-20T13:25:21+01:00';
        $jsonLD->creator = $expectedCreator;

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

        $body = $this->project(
            $eventCreated,
            $eventId,
            $metadata,
            DateTime::fromString('2015-01-20T13:25:21+01:00')
        );

        $this->assertEquals($jsonLD, $body);
    }

    public function eventCreatorDataProvider()
    {
        return [
            [
                new Metadata(
                    [
                        'user_email' => 'foo@bar.com',
                        'user_nick' => 'foo',
                        'user_id' => '123',
                    ]
                ),
                'foo@bar.com',
            ],
            [
                new Metadata(
                    [
                        'user_nick' => 'foo',
                        'user_id' => '123',
                    ]
                ),
                'foo',
            ],
        ];
    }

    /**
     * @test
     */
    public function it_handles_new_events_with_multiple_timestamps()
    {
        $eventId = '1';

        $timestamps = [
            new Timestamp(
                '2015-01-26T13:25:21+01:00',
                '2015-01-27T13:25:21+01:00'
            ),
            new Timestamp(
                '2015-01-28T13:25:21+01:00',
                '2015-01-29T13:25:21+01:00'
            ),
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
        $jsonLD->name = (object)[
            'nl' => 'some representative title'
        ];
        $jsonLD->location = (object)[
            '@type' => 'Place',
            '@id' => 'http://example.com/entity/LOCATION-ABC-123'
        ];
        $jsonLD->calendarType = 'multiple';
        $jsonLD->startDate = '2015-01-26T13:25:21+01:00';
        $jsonLD->endDate = '2015-01-29T13:25:21+01:00';
        $jsonLD->subEvent = [
            (object)[
                '@type' => 'Event',
                'startDate' => '2015-01-26T13:25:21+01:00',
                'endDate' => '2015-01-27T13:25:21+01:00',
            ],
            (object)[
                '@type' => 'Event',
                'startDate' => '2015-01-28T13:25:21+01:00',
                'endDate' => '2015-01-29T13:25:21+01:00',
            ]
        ];
        $jsonLD->sameAs = [
            'http://www.uitinvlaanderen.be/agenda/e/some-representative-title/' . $eventId,
        ];
        $jsonLD->terms = [
            (object)[
                'id' => '0.50.4.0.0',
                'label' => 'concert',
                'domain' => 'eventtype',
            ],
            (object)[
                'id' => '123',
                'label' => 'theme label',
                'domain' => 'theme',
            ]
        ];
        $jsonLD->created = '2015-01-20T13:25:21+01:00';
        $jsonLD->modified = '2015-01-20T13:25:21+01:00';

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

        $body = $this->project(
            $eventCreated,
            $eventId,
            null,
            DateTime::fromString('2015-01-20T13:25:21+01:00')
        );

        $this->assertEquals($jsonLD, $body);
    }

    /**
     * @test
     */
    public function it_strips_empty_keywords_when_importing_from_udb2()
    {
        $event = $this->cdbXMLEventFactory->eventImportedFromUDB2(
            'samples/event_with_empty_keyword.cdbxml.xml'
        );

        $body = $this->project($event, $event->getEventId());

        $expectedLabels = ['gent', 'Quiz', 'Gent on Files'];

        $this->assertEquals(
            $expectedLabels,
            $body->labels
        );
    }

    /**
     * @test
     */
    public function it_does_not_add_an_empty_labels_property()
    {
        $event = $this->cdbXMLEventFactory->eventImportedFromUDB2(
            'samples/event_without_keywords.cdbxml.xml'
        );

        $body = $this->project($event, $event->getEventId());

        $this->assertFalse(property_exists($body, 'labels'));
    }

    /**
     * @test
     */
    public function it_does_not_add_an_empty_image_property()
    {
        $event = $this->cdbXMLEventFactory->eventImportedFromUDB2(
            'samples/event_without_image.cdbxml.xml'
        );

        $body = $this->project($event, $event->getEventId());

        $this->assertObjectNotHasAttribute('image', $body);
    }

    /**
     * @test
     */
    public function it_adds_an_image_property_when_cdbxml_has_a_photo()
    {
        $event = $this->cdbXMLEventFactory->eventImportedFromUDB2(
            'samples/event_with_photo.cdbxml.xml'
        );

        $body = $this->project($event, $event->getEventId());

        $this->assertEquals(
            '//media.uitdatabank.be/20141105/ed466c72-451f-4079-94d3-4ab2e0be7b15.jpg',
            $body->image
        );
    }

    /**
     * @test
     */
    public function it_adds_a_media_object_when_an_image_is_added_to_the_event()
    {
        $eventId = 'event-1';
        $image = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new String('sexy ladies without clothes'),
            new String('Bart Ramakers'),
            Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png')
        );
        $expectedMediaObjects = [
            (object) [
                '@id' => 'http://example.com/entity/de305d54-75b4-431b-adb2-eb6b9e546014',
                '@type' => 'schema:ImageObject',
                'contentUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                'thumbnailUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                'description' => 'sexy ladies without clothes',
                'copyrightHolder' => 'Bart Ramakers'
            ]
        ];

        $imageAddedEvent = new ImageAdded($eventId, $image);
        $eventBody = $this->project($imageAddedEvent, $eventId);

        $this->assertEquals(
            $expectedMediaObjects,
            $eventBody->mediaObject
        );
    }

    /**
     * @test
     */
    public function it_adds_a_bookingInfo_property_when_cdbxml_has_pricevalue()
    {
        $event = $this->cdbXMLEventFactory->eventImportedFromUDB2(
            'samples/event_with_price_value.cdbxml.xml'
        );

        $body = $this->project($event, $event->getEventId());

        $bookingInfo = $body->bookingInfo;

        $expectedBookingInfo = new \stdClass();
        $expectedBookingInfo->priceCurrency = 'EUR';
        $expectedBookingInfo->price = 0;

        $this->assertInternalType('array', $bookingInfo);
        $this->assertCount(1, $bookingInfo);
        $this->assertEquals($expectedBookingInfo, $bookingInfo[0]);
    }

    /**
     * @test
     */
    public function it_adds_the_pricedescription_from_cdbxml_to_bookingInfo()
    {
        $event = $this->cdbXMLEventFactory->eventImportedFromUDB2(
            'samples/event_with_price_value_and_description.cdbxml.xml'
        );

        $body = $this->project($event, $event->getEventId());

        $bookingInfo = $body->bookingInfo;

        $expectedBookingInfo = new \stdClass();
        $expectedBookingInfo->priceCurrency = 'EUR';
        $expectedBookingInfo->price = 0;
        $expectedBookingInfo->description = 'Gratis voor iedereen!';

        $this->assertInternalType('array', $bookingInfo);
        $this->assertCount(1, $bookingInfo);
        $this->assertEquals($expectedBookingInfo, $bookingInfo[0]);
    }

    /**
     * @test
     */
    public function it_does_not_add_a_missing_price_from_cdbxml_to_bookingInfo()
    {
        $event = $this->cdbXMLEventFactory->eventImportedFromUDB2(
            'samples/event_with_only_price_description.cdbxml.xml'
        );

        $body = $this->project($event, $event->getEventId());

        $bookingInfo = $body->bookingInfo;

        $expectedBookingInfo = new stdClass();
        $expectedBookingInfo->description = 'Gratis voor iedereen!';

        $this->assertInternalType('array', $bookingInfo);
        $this->assertCount(1, $bookingInfo);
        $this->assertEquals($expectedBookingInfo, $bookingInfo[0]);
    }

    /**
     * @test
     */
    public function it_does_not_add_booking_info_when_price_is_missing()
    {
        $event = $this->cdbXMLEventFactory->eventImportedFromUDB2(
            'samples/event_without_price.cdbxml.xml'
        );

        $body = $this->project($event, $event->getEventId());

        $this->assertObjectNotHasAttribute('bookingInfo', $body);
    }

    /**
     * @test
     */
    public function it_does_not_add_typical_age_range_when_age_from_is_missing()
    {
        $event = $this->cdbXMLEventFactory->eventImportedFromUDB2(
            'samples/event_without_age_from.cdbxml.xml'
        );

        $body = $this->project($event, $event->getEventId());

        $this->assertObjectNotHasAttribute('typicalAgeRange', $body);
    }

    /**
     * @test
     */
    public function it_adds_typical_age_range_when_age_from_is_present()
    {
        $event = $this->cdbXMLEventFactory->eventImportedFromUDB2(
            'samples/event_with_age_from.cdbxml.xml'
        );

        $body = $this->project($event, $event->getEventId());

        $this->assertEquals('10-', $body->typicalAgeRange);
    }

    /**
     * @test
     */
    public function it_adds_a_language_property_when_cdbxml_has_languages()
    {
        $event = $this->cdbXMLEventFactory->eventImportedFromUDB2(
            'samples/event_with_languages.cdbxml.xml'
        );

        $body = $this->project($event, $event->getEventId());

        $expectedLanguages = [
            'Nederlands',
            'Frans',
            'Engels'
        ];

        $this->assertEquals(
            $expectedLanguages,
            $body->language
        );
    }

    /**
     * @test
     */
    public function it_does_not_add_an_empty_language_property()
    {
        $event = $this->cdbXMLEventFactory->eventImportedFromUDB2(
            'samples/event_without_languages.cdbxml.xml'
        );

        $body = $this->project($event, $event->getEventId());

        $this->assertObjectNotHasAttribute('language', $body);
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

        $event = $this->cdbXMLEventFactory->eventImportedFromUDB2(
            'samples/event_without_languages.cdbxml.xml'
        );

        $this->project($event, $event->getEventId());
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

        $this->documentRepository->save($initialDocument);

        $body = $this->project($eventWasLabelled, 'foo');

        $this->assertEquals(
            ['label A', 'label B'],
            $body->labels
        );
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

        $this->documentRepository->save($initialDocument);

        $eventWasUnlabelled = new Unlabelled(
            'foo',
            new Label('label B')
        );

        $body = $this->project($eventWasUnlabelled, 'foo');

        $this->assertEquals(
            ['label A', 'label C'],
            $body->labels
        );
    }

    /**
     * @test
     */
    public function it_projects_the_addition_of_a_label_to_an_event_without_existing_labels()
    {
        $initialDocument = new JsonDocument(
            'foo',
            json_encode([
                'bar' => 'stool'
            ])
        );

        $this->documentRepository->save($initialDocument);

        $eventWasLabelled = new EventWasLabelled(
            'foo',
            new Label('label B')
        );

        $body = $this->project($eventWasLabelled, 'foo');

        $expectedBody = new stdClass();
        $expectedBody->bar = 'stool';
        $expectedBody->labels = ['label B'];

        $this->assertEquals(
            $expectedBody,
            $body
        );

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

        $this->documentRepository->save($initialEventDocument);
        $this->documentRepository->save($initialSecondEventDocument);

        $expectedEventBody = (object)[
            'labels' => ['test 1', 'test 2'],
            'location' => (object)[
                'name' => "t,arsenaal mechelen",
                'address' => (object)[
                    'addressCountry' => "BE",
                    'addressLocality' => "Mechelen",
                    'postalCode' => "2800",
                    'streetAddress' => "Hanswijkstraat 63",
                ],
            ],
        ];

        $expectedSecondEventBody = (object) [
            'name' => (object)[
                'nl' => 'Quicksand Valley',
            ],
            'location' => (object)[
                'name' => "t,arsenaal mechelen",
                'address' => (object)[
                    'addressCountry' => "BE",
                    'addressLocality' => "Mechelen",
                    'postalCode' => "2800",
                    'streetAddress' => "Hanswijkstraat 63",
                ],
            ],
        ];

        $placeProjectedToJSONLD = new PlaceProjectedToJSONLD($placeID);

        $this->projector->handle(
            DomainMessage::recordNow(
                $placeProjectedToJSONLD->getId(),
                0,
                new Metadata(),
                $placeProjectedToJSONLD
            )
        );

        $this->assertEquals(
            $expectedEventBody,
            $this->getBody($eventID)
        );

        $this->assertEquals(
            $expectedSecondEventBody,
            $this->getBody($secondEventID)
        );
    }

    /**
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

        $this->documentRepository->save($initialEventDocument);
        $this->documentRepository->save($initialSecondEventDocument);

        $expectedEventBody = (object)[
            'labels' => ['beweging', 'kanker'],
            'organizer' => (object)[
                'name' => 'stichting tegen Kanker',
                'email' => [
                    'kgielens@stichtingtegenkanker.be',
                ],
            ],
        ];

        $expectedSecondEventBody = (object) [
            'name' => (object)[
                'nl' => 'Rekanto - TaiQi',
                'fr' => 'Raviva - TaiQi'
            ],
            'organizer' => (object)[
                'name' => 'stichting tegen Kanker',
                'email' => [
                    'kgielens@stichtingtegenkanker.be',
                ],
            ],
        ];

        $organizerProjectedToJSONLD = new OrganizerProjectedToJSONLD($organizerId);

        $this->projector->handle(
            DomainMessage::recordNow(
                $organizerProjectedToJSONLD->getId(),
                0,
                new Metadata(),
                $organizerProjectedToJSONLD
            )
        );

        $this->assertEquals(
            $expectedEventBody,
            $this->getBody($eventID)
        );

        $this->assertEquals(
            $expectedSecondEventBody,
            $this->getBody($secondEventID)
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

        $this->documentRepository->save($initialDocument);

        $expectedJsonLD = new stdClass();
        $expectedJsonLD->id = $id;
        $expectedJsonLD->name = (object)[
            'nl' => 'new title'
        ];
        $expectedJsonLD->location = (object)[
            '@type' => 'Place',
            '@id' => 'http://example.com/entity/LOCATION-ABC-456'
        ];
        $expectedJsonLD->calendarType = 'single';
        $expectedJsonLD->terms = [
            (object)[
                'id' => '0.50.4.0.1',
                'label' => 'concertnew',
                'domain' => 'eventtype',
            ],
            (object)[
                'id' => '123',
                'label' => 'theme label',
                'domain' => 'theme',
            ]
        ];
        $expectedJsonLD->startDate = '2015-01-26T13:25:21+01:00';
        $expectedJsonLD->endDate = '2015-02-26T13:25:21+01:00';

        $body = $this->project($majorInfoUpdated, $id);

        $this->assertEquals($expectedJsonLD, $body);
    }

    /**
     * @test
     */
    public function it_deletes_events()
    {
        $id = 'foo';

        $this->documentRepository->save(
            (new JsonDocument($id))
                ->withBody(
                    (object)[
                        'foo' => 'bar',
                    ]
                )
        );

        $eventDeleted = new EventDeleted($id);
        $this->projector->handle(
            DomainMessage::recordNow(
                $id,
                1,
                new Metadata(),
                $eventDeleted
            )
        );

        $this->setExpectedException(DocumentGoneException::class);

        $this->documentRepository->get($id);
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

        $eventId = $eventCreatedFromCdbXml->getEventId()->toNative();

        $domainMessage = new DomainMessage(
            $eventId,
            1,
            new Metadata($metadata),
            $eventCreatedFromCdbXml,
            DateTime::fromString($importedDate)
        );

        $expectedJsonLD = file_get_contents(__DIR__ . '/ReadModel/JSONLD/event_entryapi_valid_expected.json');

        $this->projector->handle($domainMessage);

        $body = $this->documentRepository->get($eventId)->getRawBody();

        $this->assertEquals(
            $expectedJsonLD,
            $body
        );
    }

    /**
     * @test
     */
    public function it_updates_events_from_cdbxml()
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

        $eventId = $eventUpdatedFromCdbXml->getEventId()->toNative();

        $domainMessage = new DomainMessage(
            $eventId,
            1,
            new Metadata($metadata),
            $eventUpdatedFromCdbXml,
            DateTime::fromString($importedDate)
        );

        $expectedJsonLD = file_get_contents(__DIR__ . '/ReadModel/JSONLD/event_entryapi_valid_expected.json');

        $this->projector->handle($domainMessage);

        $this->assertEquals(
            $expectedJsonLD,
            $this->documentRepository->get($eventId)->getRawBody()
        );
    }

    /**
     * @test
     */
    public function it_projects_a_merge_of_labels()
    {
        $initialDocument = new JsonDocument(
            'foo',
            json_encode([
                'labels' => ['label A']
            ])
        );

        $this->documentRepository->save($initialDocument);

        $labelsMerged = new LabelsMerged(
            new String('foo'),
            new LabelCollection(
                [
                    new Label('label B', true),
                    new Label('label C', false),
                ]
            )
        );

        $body = $this->project($labelsMerged, 'foo');

        $this->assertEquals(
            ['label A', 'label B', 'label C'],
            $body->labels
        );
    }

    /**
     * @test
     */
    public function it_projects_the_application_of_a_translation()
    {
        $initialDocument = new JsonDocument(
            'foo',
            json_encode([
                'name' => ['nl'=> 'Titel'],
                'description' => ['nl' => 'Omschrijving']
            ])
        );

        $this->documentRepository->save(
            $initialDocument
        );

        $translationApplied = new TranslationApplied(
            new String('foo'),
            new Language('en'),
            new String('Title'),
            new String('Short description'),
            new String('Long long long extra long description')
        );

        $expectedBody = (object)[
            'name' => (object)[
                'nl'=> 'Titel',
                'en' => 'Title'
            ],
            'description' => (object)[
                'nl' => 'Omschrijving',
                'en' => 'Long long long extra long description'
            ]
        ];

        $body = $this->project($translationApplied, 'foo');

        $this->assertEquals(
            $expectedBody,
            $body
        );
    }

    /**
     * @test
     */
    public function it_projects_the_application_of_a_title_translation()
    {
        $initialDocument = new JsonDocument(
            1,
            json_encode([
                'name' => [
                    'nl'=> 'Titel'
                ],
                'description' => [
                    'nl' => 'Omschrijving'
                ],
            ])
        );
        $this->documentRepository->save($initialDocument);

        $translationApplied = new TranslationApplied(
            new String('1'),
            new Language('en'),
            new String('Title'),
            null,
            null
        );

        $body = $this->project($translationApplied, 1);

        $this->assertEquals(
            (object)[
                'name' => (object)[
                    'nl'=> 'Titel',
                    'en' => 'Title'
                ],
                'description' => (object)[
                    'nl' => 'Omschrijving'
                ],
            ],
            $body
        );
    }

    /**
     * @test
     */
    public function it_projects_the_deletion_of_a_translation()
    {
        $initialDocument = new JsonDocument(
            'foo',
            json_encode([
                'name' => ['nl'=> 'Titel', 'en' => 'Title'],
                'description' => ['nl' => 'Omschrijving', 'en' => 'Long long long extra long description']
            ])
        );
        $this->documentRepository->save($initialDocument);

        $translationDeleted = new TranslationDeleted(
            new String('foo'),
            new Language('en')
        );

        $body = $this->project($translationDeleted, 'foo');

        $this->assertEquals(
            (object)[
                'name' => (object)[
                    'nl'=> 'Titel'
                ],
                'description' => (object)[
                    'nl' => 'Omschrijving'
                ],
            ],
            $body
        );
    }
}
