<?php

namespace CultuurNet\UDB3\Event\ReadModel\JSONLD;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CommerceGuys\Intl\Currency\CurrencyRepository;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepository;
use CultureFeed_Cdb_Data_File;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarFactory;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Cdb\CdbId\EventCdbIdExtractor;
use CultuurNet\UDB3\Cdb\PriceDescriptionParser;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Event\CdbXMLEventFactory;
use CultuurNet\UDB3\Event\Events\AudienceUpdated;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Event\Events\LabelRemoved;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\EventServiceInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ValueObjects\Audience;
use CultuurNet\UDB3\Event\ValueObjects\AudienceType;
use CultuurNet\UDB3\Iri\CallableIriGenerator;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Media\Serialization\MediaObjectSerializer;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\IriOfferIdentifierFactoryInterface;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\CdbXMLItemBaseImporter;
use CultuurNet\UDB3\OfferLDProjectorTestBase;
use CultuurNet\UDB3\Organizer\OrganizerProjectedToJSONLD;
use CultuurNet\UDB3\Place\Events\PlaceProjectedToJSONLD;
use CultuurNet\UDB3\PlaceService;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Timestamp;
use CultuurNet\UDB3\Title;
use PHPUnit_Framework_MockObject_MockObject;
use stdClass;
use Symfony\Component\Serializer\Serializer;
use ValueObjects\Geography\Country;
use ValueObjects\String\String as StringLiteral;
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
     * @var IriGeneratorInterface
     */
    private $mediaIriGenerator;

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
     * @var IriOfferIdentifierFactoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $iriOfferIdentifierFactory;

    /**
     * @var CdbXMLImporter|PHPUnit_Framework_MockObject_MockObject
     */
    protected $cdbXMLImporter;

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

        $this->eventService = $this->createMock(EventServiceInterface::class);

        $this->placeService = $this->createMock(PlaceService::class);

        $this->iriGenerator = new CallableIriGenerator(
            function ($id) {
                return 'http://example.com/entity/' . $id;
            }
        );

        $this->mediaIriGenerator = new CallableIriGenerator(function (CultureFeed_Cdb_Data_File $file) {
            return 'http://example.com/media/' . $file->getFileName();
        });

        $this->serializer = new MediaObjectSerializer($this->iriGenerator);

        $this->iriOfferIdentifierFactory = $this->createMock(IriOfferIdentifierFactoryInterface::class);
        $this->cdbXMLImporter = new CdbXMLImporter(
            new CdbXMLItemBaseImporter($this->mediaIriGenerator),
            new EventCdbIdExtractor(),
            new PriceDescriptionParser(
                new NumberFormatRepository(),
                new CurrencyRepository()
            ),
            new CalendarFactory()
        );

        $this->projector = new EventLDProjector(
            $this->documentRepository,
            $this->iriGenerator,
            $this->eventService,
            $this->placeService,
            $this->organizerService,
            $this->serializer,
            $this->iriOfferIdentifierFactory,
            $this->cdbXMLImporter
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
            new Location(
                '395fe7eb-9bac-4647-acae-316b6446a85e',
                new StringLiteral('Repeteerkot'),
                new Address(
                    new Street('Kerkstraat 69'),
                    new PostalCode('9620'),
                    new Locality('Zottegem'),
                    Country::fromNative('BE')
                )
            ),
            new Calendar(
                CalendarType::SINGLE(),
                \DateTime::createFromFormat(\DateTime::ATOM, '2015-01-26T13:25:21+01:00')
            )
        );

        $jsonLD = new stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/1';
        $jsonLD->{'@context'} = '/contexts/event';
        $jsonLD->name = (object)[
            'nl' => 'some representative title'
        ];
        $jsonLD->location = (object)[
            '@type' => 'Place',
            '@id' => 'http://example.com/entity/395fe7eb-9bac-4647-acae-316b6446a85e'
        ];
        $jsonLD->calendarType = 'single';
        $jsonLD->startDate = '2015-01-26T13:25:21+01:00';
        $jsonLD->availableTo = $jsonLD->startDate;
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
        $jsonLD->workflowStatus = 'DRAFT';
        $jsonLD->audience = (object)['audienceType' => 'everyone'];

        // Set up the placeService so that it does not know about the JSON-LD
        // representation of the Place yet and only returns the URI of the
        // Place.
        $this->placeService->expects($this->once())
            ->method('getEntity')
            ->with('395fe7eb-9bac-4647-acae-316b6446a85e')
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
            new Location(
                '395fe7eb-9bac-4647-acae-316b6446a85e',
                new StringLiteral('Repeteerkot'),
                new Address(
                    new Street('Kerkstraat 69'),
                    new PostalCode('9620'),
                    new Locality('Zottegem'),
                    Country::fromNative('BE')
                )
            ),
            new Calendar(
                CalendarType::SINGLE(),
                \DateTime::createFromFormat(\DateTime::ATOM, '2015-01-26T13:25:21+01:00')
            ),
            new Theme('123', 'theme label')
        );

        $jsonLD = new stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $eventId;
        $jsonLD->{'@context'} = '/contexts/event';
        $jsonLD->name = (object)[
            'nl' => 'some representative title'
        ];
        $jsonLD->location = (object)[
            '@type' => 'Place',
            '@id' => 'http://example.com/entity/395fe7eb-9bac-4647-acae-316b6446a85e'
        ];
        $jsonLD->calendarType = 'single';
        $jsonLD->startDate = '2015-01-26T13:25:21+01:00';
        $jsonLD->availableTo = $jsonLD->startDate;
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
        $jsonLD->workflowStatus = 'DRAFT';
        $jsonLD->audience = (object)['audienceType' => 'everyone'];

        // Set up the placeService so that it does not know about the JSON-LD
        // representation of the Place yet and only returns the URI of the
        // Place.
        $this->placeService->expects($this->once())
            ->method('getEntity')
            ->with('395fe7eb-9bac-4647-acae-316b6446a85e')
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
            new Location(
                '395fe7eb-9bac-4647-acae-316b6446a85e',
                new StringLiteral('Repeteerkot'),
                new Address(
                    new Street('Kerkstraat 69'),
                    new PostalCode('9620'),
                    new Locality('Zottegem'),
                    Country::fromNative('BE')
                )
            ),
            new Calendar(
                CalendarType::SINGLE(),
                \DateTime::createFromFormat(\DateTime::ATOM, '2015-01-26T13:25:21+01:00')
            ),
            new Theme('123', 'theme label')
        );

        $jsonLD = new stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $eventId;
        $jsonLD->{'@context'} = '/contexts/event';
        $jsonLD->name = (object)[
            'nl' => 'some representative title'
        ];
        $jsonLD->location = (object)[
            '@type' => 'Place',
            '@id' => 'http://example.com/entity/395fe7eb-9bac-4647-acae-316b6446a85e'
        ];
        $jsonLD->calendarType = 'single';
        $jsonLD->startDate = '2015-01-26T13:25:21+01:00';
        $jsonLD->availableTo = $jsonLD->startDate;
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
        $jsonLD->workflowStatus = 'DRAFT';
        $jsonLD->audience = (object)['audienceType' => 'everyone'];

        // Set up the placeService so that it does not know about the JSON-LD
        // representation of the Place yet and only returns the URI of the
        // Place.
        $this->placeService->expects($this->once())
            ->method('getEntity')
            ->with('395fe7eb-9bac-4647-acae-316b6446a85e')
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
        $eventId = '926fca95-010e-46b1-8b8e-abe757dd32d5';

        $timestamps = [
            new Timestamp(
                \DateTime::createFromFormat(\DateTime::ATOM, '2015-01-26T13:25:21+01:00'),
                \DateTime::createFromFormat(\DateTime::ATOM, '2015-01-27T13:25:21+01:00')
            ),
            new Timestamp(
                \DateTime::createFromFormat(\DateTime::ATOM, '2015-01-28T13:25:21+01:00'),
                \DateTime::createFromFormat(\DateTime::ATOM, '2015-01-29T13:25:21+01:00')
            ),
        ];
        $eventCreated = new EventCreated(
            $eventId,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Location(
                '395fe7eb-9bac-4647-acae-316b6446a85e',
                new StringLiteral('Repeteerkot'),
                new Address(
                    new Street('Kerkstraat 69'),
                    new PostalCode('9620'),
                    new Locality('Zottegem'),
                    Country::fromNative('BE')
                )
            ),
            new Calendar(
                CalendarType::MULTIPLE(),
                \DateTime::createFromFormat(\DateTime::ATOM, '2015-01-26T13:25:21+01:00'),
                \DateTime::createFromFormat(\DateTime::ATOM, '2015-01-29T13:25:21+01:00'),
                $timestamps
            ),
            new Theme('123', 'theme label')
        );

        $jsonLD = new stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $eventId;
        $jsonLD->{'@context'} = '/contexts/event';
        $jsonLD->name = (object)[
            'nl' => 'some representative title'
        ];
        $jsonLD->location = (object)[
            '@type' => 'Place',
            '@id' => 'http://example.com/entity/395fe7eb-9bac-4647-acae-316b6446a85e'
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
        $jsonLD->availableTo = $jsonLD->endDate;
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
        $jsonLD->workflowStatus = 'DRAFT';
        $jsonLD->audience = (object)['audienceType' => 'everyone'];

        // Set up the placeService so that it does not know about the JSON-LD
        // representation of the Place yet and only returns the URI of the
        // Place.
        $this->placeService->expects($this->once())
            ->method('getEntity')
            ->with('395fe7eb-9bac-4647-acae-316b6446a85e')
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
    public function it_doesnt_remove_existing_location_when_updating_from_udb2()
    {
        $event = $this->cdbXMLEventFactory->eventUpdatedFromUDB2(
            'samples/event_with_udb3_place.cdbxml.xml'
        );

        // add the event json to memory
        $this->documentRepository->save(new JsonDocument(
            'someId',
            file_get_contents(
                __DIR__ . '/../../samples/event_with_udb3_place.json'
            )
        ));

        $body = $this->project($event, $event->getEventId());

        // asset the location is still a place object
        $this->assertEquals("Place", $body->location->{'@type'});
        $this->assertEquals(
            "http://culudb-silex.dev:8080/place/f31033c4-96b1-4012-99ac-4439c614f701",
            $body->location->{'@id'}
        );
    }

    /**
     * @test
     */
    public function it_can_update_an_event_from_udb2_even_if_it_has_been_deleted()
    {
        $event = $this->cdbXMLEventFactory->eventImportedFromUDB2(
            'samples/event_with_empty_keyword.cdbxml.xml'
        );
        $eventId = $event->getEventId();

        $this->project($event, $event->getEventId());

        $eventDeleted = new EventDeleted($eventId);

        $this->project($eventDeleted, $eventDeleted->getItemId(), null, null, false);

        $eventUpdatedFromUdb2 = $this->cdbXMLEventFactory->eventUpdatedFromUDB2(
            'samples/event_with_empty_keyword.cdbxml.xml'
        );
        $this->project($eventUpdatedFromUdb2, $eventUpdatedFromUdb2->getEventId());
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

        $this->assertInternalType('object', $bookingInfo);
        $this->assertEquals($expectedBookingInfo, $bookingInfo);
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

        $this->assertInternalType('object', $bookingInfo);
        $this->assertEquals($expectedBookingInfo, $bookingInfo);
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

        $this->assertInternalType('object', $bookingInfo);
        $this->assertEquals($expectedBookingInfo, $bookingInfo);
    }

    /**
     * @test
     */
    public function it_does_not_add_booking_info_when_price_and_reservation_contact_channels_are_missing()
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

        $this->assertEquals('10-12', $body->typicalAgeRange);
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
    public function it_projects_the_addition_of_a_label()
    {
        $labelAdded = new LabelAdded(
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

        $body = $this->project($labelAdded, 'foo');

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

        $labelRemoved = new LabelRemoved(
            'foo',
            new Label('label B')
        );

        $body = $this->project($labelRemoved, 'foo');

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

        $labelAdded = new LabelAdded(
            'foo',
            new Label('label B')
        );

        $body = $this->project($labelAdded, 'foo');

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
        $placeIri = Url::fromNative('http://du.de/place/' . $placeID);

        $placeIdentifier = new IriOfferIdentifier($placeIri, $placeID, OfferType::PLACE());

        $this->iriOfferIdentifierFactory->expects($this->once())
            ->method('fromIri')
            ->with($placeIri)
            ->willReturn($placeIdentifier);

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

        $placeProjectedToJSONLD = new PlaceProjectedToJSONLD((string) $placeIri);

        $this->projector->handle(
            DomainMessage::recordNow(
                $placeID,
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
            ->with('395fe7eb-9bac-4647-acae-316b6446a85e')
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
        $location = new Location(
            '395fe7eb-9bac-4647-acae-316b6446a85e',
            new StringLiteral('Repeteerkot'),
            new Address(
                new Street('Kerkstraat 69'),
                new PostalCode('9620'),
                new Locality('Zottegem'),
                Country::fromNative('BE')
            )
        );
        $calendar = new Calendar(
            CalendarType::SINGLE(),
            \DateTime::createFromFormat(\DateTime::ATOM, '2015-01-26T13:25:21+01:00'),
            \DateTime::createFromFormat(\DateTime::ATOM, '2015-02-26T13:25:21+01:00')
        );
        $theme = new Theme('123', 'theme label');
        $majorInfoUpdated = new MajorInfoUpdated($id, $title, $eventType, $location, $calendar, $theme);

        $jsonLD = new stdClass();
        $jsonLD->id = $id;
        $jsonLD->name = ['nl' => 'some representative title'];
        $jsonLD->location = [
            '@type' => 'Place',
            '@id' => 'http://example.com/entity/395fe7eb-9bac-4647-acae-316b6446a85e'
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
            '@id' => 'http://example.com/entity/395fe7eb-9bac-4647-acae-316b6446a85e'
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
        $expectedJsonLD->availableTo = $expectedJsonLD->startDate;

        $body = $this->project($majorInfoUpdated, $id);

        $this->assertEquals($expectedJsonLD, $body);
    }

    /**
     * @test
     */
    public function it_projects_updating_audience()
    {
        $eventId = 'd2b41f1d-598c-46af-a3a5-10e373faa6fe';

        $audienceUpdated = new AudienceUpdated(
            $eventId,
            new Audience(AudienceType::EDUCATION())
        );

        $body = $this->project($audienceUpdated, $eventId);

        $expectedJson = (object) [
                '@id' => 'http://example.com/entity/' . $eventId,
                '@context' => '/contexts/event',
                'audience' => (object) ['audienceType' => 'education']
            ];

        $this->assertEquals($expectedJson, $body);
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
     * @dataProvider eventUpdateDataProvider
     * @param $documentWithUDB3Media
     * @param $domainMessage
     * @param $expectedMediaObjects
     */
    public function it_prioritizes_udb3_media_when_updating_an_event(
        $documentWithUDB3Media,
        $domainMessage,
        $expectedMediaObjects
    ) {
        $this->documentRepository->save($documentWithUDB3Media);

        $this->projector->handle($domainMessage);

        $this->assertEquals(
            $expectedMediaObjects,
            $this->documentRepository->get('someId')->getBody()->mediaObject
        );
    }

    public function eventUpdateDataProvider()
    {
        $documentWithUDB3Media = new JsonDocument(
            'someId',
            json_encode([
                'mediaObject' => [
                    [
                        '@id' => 'http://example.com/entity/de305d54-75b4-431b-adb2-eb6b9e546014',
                        '@type' => 'schema:ImageObject',
                        'contentUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                        'thumbnailUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                        'description' => 'sexy ladies without clothes',
                        'copyrightHolder' => 'Bart Ramakers'
                    ]
                ]
            ])
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

        $importedDate = '2015-03-01T10:17:19.176169+02:00';

        $metadata = array();
        $metadata['user_nick'] = 'Jantest';
        $metadata['consumer']['name'] = 'UiTDatabank';

        $eventUpdatedFromUDB2 = new EventUpdatedFromUDB2(
            'foo',
            file_get_contents(__DIR__ . '/../../samples/event_with_photo.cdbxml.xml'),
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
        );

        return [
            'udb2' => [
                $documentWithUDB3Media,
                new DomainMessage(
                    'dcd1ef37-0608-4824-afe3-99124feda64b',
                    1,
                    new Metadata($metadata),
                    $eventUpdatedFromUDB2,
                    DateTime::fromString($importedDate)
                ),
                $expectedMediaObjects
            ]
        ];
    }
}
