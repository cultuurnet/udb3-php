<?php

namespace CultuurNet\UDB3\Place\ReadModel\JSONLD;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\Serializer\SerializerInterface;
use CultureFeed_Cdb_Data_File;
use CultuurNet\Geocoding\Coordinate\Coordinates;
use CultuurNet\Geocoding\Coordinate\Latitude;
use CultuurNet\Geocoding\Coordinate\Longitude;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarFactory;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Facility;
use CultuurNet\UDB3\Iri\CallableIriGenerator;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Media\Serialization\MediaObjectSerializer;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\CdbXmlContactInfoImporter;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\CdbXMLItemBaseImporter;
use CultuurNet\UDB3\OfferLDProjectorTestBase;
use CultuurNet\UDB3\Place\Events\FacilitiesUpdated;
use CultuurNet\UDB3\Place\Events\GeoCoordinatesUpdated;
use CultuurNet\UDB3\Place\Events\LabelAdded;
use CultuurNet\UDB3\Place\Events\LabelRemoved;
use CultuurNet\UDB3\Place\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceUpdatedFromUDB2;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use PHPUnit_Framework_MockObject_MockObject;
use stdClass;
use ValueObjects\Geography\Country;

class PlaceLDProjectorTest extends OfferLDProjectorTestBase
{
    /**
     * @var PlaceLDProjector
     */
    protected $projector;

    /**
     * @var DocumentRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentRepository;

    /**
     * @var IriGeneratorInterface
     */
    private $iriGenerator;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Address
     */
    private $address;

    /**
     * @var CdbXMLImporter
     */
    private $cdbXMLImporter;

    /**
     * @var IriGeneratorInterface
     */
    private $mediaIriGenerator;

    /**
     * Constructs a test case with the given name.
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName, 'CultuurNet\\UDB3\\Place');
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->iriGenerator = new CallableIriGenerator(
            function ($id) {
                return 'http://example.com/entity/' . $id;
            }
        );

        $this->serializer = new MediaObjectSerializer($this->iriGenerator);

        $this->mediaIriGenerator = new CallableIriGenerator(function (CultureFeed_Cdb_Data_File $file) {
            return 'http://example.com/media/' . $file->getFileName();
        });

        $this->cdbXMLImporter = new CdbXMLImporter(
            new CdbXMLItemBaseImporter($this->mediaIriGenerator),
            new CalendarFactory(),
            new CdbXmlContactInfoImporter()
        );

        $this->projector = new PlaceLDProjector(
            $this->documentRepository,
            $this->iriGenerator,
            $this->organizerService,
            $this->serializer,
            $this->cdbXMLImporter
        );

        $street = new Street('Kerkstraat 69');
        $locality = new Locality('Leuven');
        $postalCode = new PostalCode('3000');
        $country = Country::fromNative('BE');

        $this->address = new Address($street, $postalCode, $locality, $country);
    }

    /**
     * @param string $fileName
     * @return PlaceImportedFromUDB2
     */
    private function placeImportedFromUDB2($fileName)
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/' . $fileName
        );
        $event = new PlaceImportedFromUDB2(
            'someId',
            $cdbXml,
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
        );

        return $event;
    }

    /**
     * @test
     */
    public function it_handles_new_places_without_theme()
    {
        $id = 'foo';
        $created = '2015-01-20T13:25:21+01:00';

        $placeCreated = new PlaceCreated(
            $id,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            $this->address,
            new Calendar(CalendarType::PERMANENT())
        );

        $jsonLD = new stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $id;
        $jsonLD->{'@context'} = '/contexts/place';
        $jsonLD->name = (object)[ 'nl' => 'some representative title' ];
        $jsonLD->address = (object)[
          'addressCountry' => 'BE',
          'addressLocality' => 'Leuven',
          'postalCode' => '3000',
          'streetAddress' => 'Kerkstraat 69',
        ];
        $jsonLD->calendarType = 'permanent';
        $jsonLD->availableTo = '2100-01-01T00:00:00+00:00';
        $jsonLD->terms = [
            (object)[
                'id' => '0.50.4.0.0',
                'label' => 'concert',
                'domain' => 'eventtype',
            ]
        ];
        $jsonLD->created = $created;
        $jsonLD->modified = $created;
        $jsonLD->workflowStatus = 'DRAFT';

        $body = $this->project(
            $placeCreated,
            $id,
            null,
            DateTime::fromString($created)
        );

        $this->assertEquals(
            $jsonLD,
            $body
        );
    }

    /**
     * @test
     */
    public function it_handles_new_places_with_theme()
    {
        $id = 'bar';
        $created = '2015-01-20T13:25:21+01:00';

        $placeCreated = new PlaceCreated(
            $id,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            $this->address,
            new Calendar(CalendarType::PERMANENT()),
            new Theme('123', 'theme label')
        );

        $jsonLD = new stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $id;
        $jsonLD->{'@context'} = '/contexts/place';
        $jsonLD->name = (object)[ 'nl' => 'some representative title' ];
        $jsonLD->address = (object)[
            'addressCountry' => 'BE',
            'addressLocality' => 'Leuven',
            'postalCode' => '3000',
            'streetAddress' => 'Kerkstraat 69',
        ];
        $jsonLD->calendarType = 'permanent';
        $jsonLD->availableTo = '2100-01-01T00:00:00+00:00';
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
        $jsonLD->created = $created;
        $jsonLD->modified = $created;
        $jsonLD->workflowStatus = 'DRAFT';

        $body = $this->project(
            $placeCreated,
            $id,
            null,
            DateTime::fromString($created)
        );

        $this->assertEquals(
            $jsonLD,
            $body
        );
    }

    /**
     * @test
     */
    public function it_handles_new_places_with_creator()
    {
        $id = 'foo';
        $created = '2015-01-20T13:25:21+01:00';

        $placeCreated = new PlaceCreated(
            $id,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            $this->address,
            new Calendar(CalendarType::PERMANENT())
        );

        $jsonLD = new stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $id;
        $jsonLD->{'@context'} = '/api/1.0/place.jsonld';
        $jsonLD->name = 'some representative title';
        $jsonLD->address = (object)[
            'addressCountry' => 'BE',
            'addressLocality' => 'Leuven',
            'postalCode' => '3000',
            'streetAddress' => 'Kerkstraat 69',
        ];
        $jsonLD->calendarType = 'permanent';
        $jsonLD->terms = [
            (object)[
                'id' => '0.50.4.0.0',
                'label' => 'concert',
                'domain' => 'eventtype',
            ]
        ];
        $jsonLD->created = $created;
        $jsonLD->modified = $created;
        $jsonLD->creator = '1 (Tester)';
        $jsonLD->workflowStatus = 'READY_FOR_VALIDATION';

        $metadata = new Metadata(
            [
                'user_id' => '1',
                'user_nick' => 'Tester'
            ]
        );
        $this->project(
            $placeCreated,
            $id,
            $metadata,
            DateTime::fromString($created)
        );
    }

    /**
     * @test
     */
    public function it_does_not_add_an_empty_image_property()
    {
        $event = $this->placeImportedFromUDB2('place_without_image.cdbxml.xml');

        $body = $this->project($event, $event->getActorId());

        $this->assertObjectNotHasAttribute('image', $body);
    }

    /**
     * @return array
     */
    public function descriptionSamplesProvider()
    {
        $samples = array(
            ['place_with_short_description.cdbxml.xml', 'Korte beschrijving.'],
            ['place_with_long_description.cdbxml.xml', 'Lange beschrijving.'],
            ['place_with_short_and_long_description.cdbxml.xml', "Korte beschrijving.<br/>Lange beschrijving."]
        );

        return $samples;
    }

    /**
     * @test
     */
    public function it_updates_a_place_from_udb2()
    {
        $placeImportedFromUdb2 = $this->placeImportedFromUDB2('place_with_short_description.cdbxml.xml');
        $actorId = $placeImportedFromUdb2->getActorId();

        $cdbXml = file_get_contents(__DIR__ . '/place_with_short_and_long_description.cdbxml.xml');
        $placeUpdatedFromUdb2 = new PlaceUpdatedFromUDB2(
            $actorId,
            $cdbXml,
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
        );

        $body = $this->project($placeUpdatedFromUdb2, $actorId);

        $this->assertEquals('Korte beschrijving.<br/>Lange beschrijving.', $body->description->nl);
    }

    /**
     * @test
     */
    public function it_updates_a_place_from_udb2_when_it_has_been_deleted_in_udb3()
    {
        $placeImportedFromUdb2 = $this->placeImportedFromUDB2('place_with_short_description.cdbxml.xml');
        $actorId = $placeImportedFromUdb2->getActorId();

        $placeDeleted = new PlaceDeleted($actorId);
        $this->project($placeDeleted, $actorId, null, null, false);

        $cdbXml = file_get_contents(__DIR__ . '/place_with_short_and_long_description.cdbxml.xml');
        $placeUpdatedFromUdb2 = new PlaceUpdatedFromUDB2(
            $actorId,
            $cdbXml,
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
        );

        $body = $this->project($placeUpdatedFromUdb2, $actorId);

        $this->assertEquals('Korte beschrijving.<br/>Lange beschrijving.', $body->description->nl);

    }

    /**
     * @test
     * @dataProvider descriptionSamplesProvider
     * @param string $fileName
     * @param string $expectedDescription
     */
    public function it_adds_a_description_property_when_cdbxml_has_long_or_short_description($fileName, $expectedDescription)
    {
        $event = $this->placeImportedFromUDB2($fileName);

        $body = $this->project($event, $event->getActorId());

        $this->assertEquals(
            $expectedDescription,
            $body->description->nl
        );
    }

    /**
     * @test
     */
    public function it_projects_the_updating_of_major_info()
    {
        $id = 'foo';
        $title = new Title('new title');
        $eventType = new EventType('0.50.4.0.1', 'concertnew');
        $calendar = new Calendar(
            CalendarType::SINGLE(),
            \DateTime::createFromFormat(\DateTime::ATOM, '2015-01-26T13:25:21+01:00'),
            \DateTime::createFromFormat(\DateTime::ATOM, '2015-02-26T13:25:21+01:00')
        );
        $theme = new Theme('123', 'theme label');
        $majorInfoUpdated = new MajorInfoUpdated($id, $title, $eventType, $this->address, $calendar, $theme);

        $jsonLD = new stdClass();
        $jsonLD->id = $id;
        $jsonLD->name = (object)['nl'=>'some representative title'];
        $jsonLD->address = (object)[
          'addressCountry' => '$country',
          'addressLocality' => '$locality',
          'postalCode' => '$postalCode',
          'streetAddress' => '$street',
        ];
        $jsonLD->calendarType = 'permanent';
        $jsonLD->terms = [
            (object)[
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
        $expectedJsonLD->name = (object)['nl'=>'new title'];
        $expectedJsonLD->address = (object)[
            'addressCountry' => 'BE',
            'addressLocality' => 'Leuven',
            'postalCode' => '3000',
            'streetAddress' => 'Kerkstraat 69',
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
        $expectedJsonLD->availableTo = $expectedJsonLD->endDate;

        $body = $this->project($majorInfoUpdated, $majorInfoUpdated->getPlaceId());
        $this->assertEquals($expectedJsonLD, $body);
    }

    /**
     * @test
     */
    public function it_projects_the_updating_of_facilities()
    {

        $id = 'foo';
        $facilities = [
            new Facility('facility1', 'facility label'),
            new Facility('facility2', 'facility label2'),
        ];

        $facilitiesUpdated = new FacilitiesUpdated($id, $facilities);

        $initialDocument = new JsonDocument(
            $id,
            json_encode([
                'terms' => [
                    [
                        'id' => 'facility1',
                        'label' => 'facility label',
                        'domain' => 'facility',
                    ]
                ]
            ])
        );

        $this->documentRepository->save($initialDocument);

        $expectedBody = (object)[
            'terms' => [
                (object)[
                    'id' => 'facility1',
                    'label' => 'facility label',
                    'domain' => 'facility',
                ],
                (object)[
                    'id' => 'facility2',
                    'label' => 'facility label2',
                    'domain' => 'facility',
                ]
            ]
        ];

        $body = $this->project($facilitiesUpdated, $id);
        $this->assertEquals($expectedBody, $body);
    }

    /**
     * @test
     */
    public function it_projects_the_updating_of_geo_coordinates()
    {
        $id = 'ea328f14-a3c8-4f71-abd9-00cd0a2cf217';

        $initialDocument = new JsonDocument(
            $id,
            json_encode(
                [
                    '@id' => 'http://uitdatabank/place/' . $id,
                    '@type' => 'Place',
                    'name' => 'Test',
                ]
            )
        );

        $this->documentRepository->save($initialDocument);

        $coordinatesUpdated = new GeoCoordinatesUpdated(
            $id,
            new Coordinates(
                new Latitude(1.1234567),
                new Longitude(-0.34567)
            )
        );

        $expectedBody = (object) [
            '@id' => 'http://uitdatabank/place/' . $id,
            '@type' => 'Place',
            'name' => 'Test',
            'geo' => (object) [
                'latitude' => 1.1234567,
                'longitude' => -0.34567,
            ],
        ];

        $body = $this->project($coordinatesUpdated, $id);
        $this->assertEquals($expectedBody, $body);
    }

    /**
     * @test
     */
    public function it_deletes_places()
    {
        $id = 'foo';

        $placeDeleted = new PlaceDeleted($id);

        $this->projector->handle(
            DomainMessage::recordNow(
                $id,
                2,
                new Metadata(),
                $placeDeleted
            )
        );

        $this->expectException(DocumentGoneException::class);

        $this->documentRepository->get($id);
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
    public function it_projects_the_addition_of_a_label_to_a_place_without_existing_labels()
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
    public function it_removes_geocoordinates_after_major_info_updated()
    {
        $initialDocument = new JsonDocument(
            '3c4850d7-689a-4729-8c5f-5f6c172ba52d',
            json_encode(
                [
                    'name' => [
                        'nl' => 'Old title',
                    ],
                    'geo' => [
                        'latitude' => 1.5678,
                        'longitude' => -0.9524,
                    ],
                    'terms' => [],
                ]
            )
        );

        $this->documentRepository->save($initialDocument);

        $majorInfoUpdated = new MajorInfoUpdated(
            '3c4850d7-689a-4729-8c5f-5f6c172ba52d',
            new Title('New title'),
            new EventType('1.0.0.0', 'Mock'),
            new Address(
                new Street('Natieplein 2'),
                new PostalCode('1000'),
                new Locality('Brussel'),
                Country::fromNative('BE')
            ),
            new Calendar(CalendarType::PERMANENT())
        );

        $body = $this->project($majorInfoUpdated, '3c4850d7-689a-4729-8c5f-5f6c172ba52d');

        $this->assertArrayNotHasKey('geo', (array) $body);
    }

    /**
     * @test
     */
    public function it_removes_geocoordinates_after_place_updated_from_udb2()
    {
        $initialDocument = new JsonDocument(
            '318F2ACB-F612-6F75-0037C9C29F44087A',
            json_encode(
                [
                    'name' => [
                        'nl' => 'Old title',
                    ],
                    'geo' => [
                        'latitude' => 1.5678,
                        'longitude' => -0.9524,
                    ],
                    'terms' => [],
                ]
            )
        );

        $this->documentRepository->save($initialDocument);

        $cdbXml = file_get_contents(__DIR__ . '/place_with_long_description.cdbxml.xml');
        $placeUpdatedFromUdb2 = new PlaceUpdatedFromUDB2(
            '318F2ACB-F612-6F75-0037C9C29F44087A',
            $cdbXml,
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
        );

        $body = $this->project($placeUpdatedFromUdb2, '318F2ACB-F612-6F75-0037C9C29F44087A');

        $this->assertArrayNotHasKey('geo', (array) $body);
    }
}
