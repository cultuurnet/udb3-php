<?php

namespace CultuurNet\UDB3\Place;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventBusInterface;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Facility;
use CultuurNet\UDB3\Iri\CallableIriGenerator;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\Place\Events\FacilitiesUpdated;
use CultuurNet\UDB3\Place\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use stdClass;

class PlaceLDProjectorTest extends PHPUnit_Framework_TestCase
{

    use \CultuurNet\UDB3\OfferLDProjectorTestTrait;

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
     * @var OrganizerService|PHPUnit_Framework_MockObject_MockObject
     */
    private $organizerService;

    public function setUp()
    {

        $this->organizerService = $this->getMock(
            OrganizerService::class,
            array(),
            array(),
            '',
            false
        );

        $this->documentRepository = $this->getMock(DocumentRepositoryInterface::class);
        $this->iriGenerator = new CallableIriGenerator(
            function ($id) {
                return 'http://example.com/entity/' . $id;
            }
        );

        $this->projector = new PlaceLDProjector(
            $this->documentRepository,
            $this->iriGenerator,
            $this->organizerService,
            $this->getMock(EventBusInterface::class)
        );

    }

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
        $uuidGenerator = new Version4Generator();
        $id = $uuidGenerator->generate();
        $created = '2015-01-20T13:25:21+01:00';

        $placeCreated = new PlaceCreated(
            $id,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Address('$street', '$postalCode', '$locality', '$country'),
            new Calendar('permanent')
        );

        $jsonLD = new stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $id;
        $jsonLD->{'@context'} = '/api/1.0/place.jsonld';
        $jsonLD->name = 'some representative title';
        $jsonLD->address = [
          'addressCountry' => '$country',
          'addressLocality' => '$locality',
          'postalCode' => '$postalCode',
          'streetAddress' => '$street',
        ];
        $jsonLD->calendarType = 'permanent';
        $jsonLD->terms = [
            [
                'id' => '0.50.4.0.0',
                'label' => 'concert',
                'domain' => 'eventtype',
            ]
        ];
        $jsonLD->created = $created;

        $expectedDocument = (new JsonDocument($id))
            ->withBody($jsonLD);

        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with($expectedDocument);

        $this->projector->handle(
            new DomainMessage(
                1,
                1,
                new Metadata(),
                $placeCreated,
                DateTime::fromString($created)
            )
        );
    }

    /**
     * @test
     */
    public function it_handles_new_places_with_theme()
    {
        $uuidGenerator = new Version4Generator();
        $id = $uuidGenerator->generate();
        $created = '2015-01-20T13:25:21+01:00';

        $placeCreated = new PlaceCreated(
            $id,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Address('$street', '$postalCode', '$locality', '$country'),
            new Calendar('permanent'),
            new Theme('123', 'theme label')
        );

        $jsonLD = new stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $id;
        $jsonLD->{'@context'} = '/api/1.0/place.jsonld';
        $jsonLD->name = 'some representative title';
        $jsonLD->address = [
          'addressCountry' => '$country',
          'addressLocality' => '$locality',
          'postalCode' => '$postalCode',
          'streetAddress' => '$street',
        ];
        $jsonLD->calendarType = 'permanent';
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
        $jsonLD->created = $created;

        $expectedDocument = (new JsonDocument($id))
            ->withBody($jsonLD);

        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with($expectedDocument);

        $this->projector->handle(
            new DomainMessage(
                1,
                1,
                new Metadata(),
                $placeCreated,
                DateTime::fromString($created)
            )
        );
    }

    /**
     * @test
     */
    public function it_handles_new_places_with_creator()
    {
        $uuidGenerator = new Version4Generator();
        $id = $uuidGenerator->generate();
        $created = '2015-01-20T13:25:21+01:00';

        $placeCreated = new PlaceCreated(
            $id,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Address('$street', '$postalCode', '$locality', '$country'),
            new Calendar('permanent')
        );

        $jsonLD = new stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $id;
        $jsonLD->{'@context'} = '/api/1.0/place.jsonld';
        $jsonLD->name = 'some representative title';
        $jsonLD->address = [
          'addressCountry' => '$country',
          'addressLocality' => '$locality',
          'postalCode' => '$postalCode',
          'streetAddress' => '$street',
        ];
        $jsonLD->calendarType = 'permanent';
        $jsonLD->terms = [
            [
                'id' => '0.50.4.0.0',
                'label' => 'concert',
                'domain' => 'eventtype',
            ]
        ];
        $jsonLD->created = $created;
        $jsonLD->creator = '1 (Tester)';

        $expectedDocument = (new JsonDocument($id))
            ->withBody($jsonLD);

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
                $placeCreated,
                DateTime::fromString($created)
            )
        );
    }

    /**
     * @test
     */
    public function it_does_not_add_an_empty_image_property()
    {
        $event = $this->placeImportedFromUDB2('place_without_image.cdbxml.xml');

        $this->documentRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (JsonDocument $document) {
                $body = $document->getBody();
                return !property_exists($body, 'image');
            }));

        $this->projector->applyPlaceImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_adds_an_image_property_when_cdbxml_has_a_photo()
    {
        $event = $this->placeImportedFromUDB2('place_with_image.cdbxml.xml');

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

        $this->projector->applyPlaceImportedFromUDB2($event);
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
     * @dataProvider descriptionSamplesProvider
     */
    public function it_adds_a_description_property_when_cdbxml_has_long_or_short_description($fileName, $expectedDescription)
    {
        $event = $this->placeImportedFromUDB2($fileName);

        $this->documentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (JsonDocument $jsonDocument) use ($expectedDescription) {
                        $body = $jsonDocument->getBody();

                        return $body->description === $expectedDescription;
                    }
                )
            );

        $this->projector->applyPlaceImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_projects_the_updating_of_major_info()
    {

        $id = 'foo';
        $title = new Title('new title');
        $eventType = new EventType('0.50.4.0.1', 'concertnew');
        $address = new Address('$newStreet', '$newPostalCode', '$newLocality', '$newCountry');
        $calendar = new Calendar('single', '2015-01-26T13:25:21+01:00', '2015-02-26T13:25:21+01:00');
        $theme = new Theme('123', 'theme label');
        $majorInfoUpdated = new MajorInfoUpdated($id, $title, $eventType, $address, $calendar, $theme);

        $jsonLD = new stdClass();
        $jsonLD->id = $id;
        $jsonLD->name = 'some representative title';
        $jsonLD->address = [
          'addressCountry' => '$country',
          'addressLocality' => '$locality',
          'postalCode' => '$postalCode',
          'streetAddress' => '$street',
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
        $expectedJsonLD->name = 'new title';
        $expectedJsonLD->address = [
          'addressCountry' => '$newCountry',
          'addressLocality' => '$newLocality',
          'postalCode' => '$newPostalCode',
          'streetAddress' => '$newStreet',
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

        $expectedDocument = new JsonDocument(
            $id,
            json_encode([
                'terms' => [
                    [
                        'id' => 'facility1',
                        'label' => 'facility label',
                        'domain' => 'facility',
                    ],
                    [
                        'id' => 'facility2',
                        'label' => 'facility label2',
                        'domain' => 'facility',
                    ]
                ]
            ])
        );

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

        $this->projector->applyFacilitiesUpdated($facilitiesUpdated);
    }

    /**
     * @test
     */
    public function it_deletes_places()
    {

        $id = 'foo';
        $this->documentRepository->expects($this->once())
            ->method('remove')
            ->with($id);

        $placeDeleted = new PlaceDeleted($id);
        $this->projector->applyPlaceDeleted($placeDeleted);

    }
}
