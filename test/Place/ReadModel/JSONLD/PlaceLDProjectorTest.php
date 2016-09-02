<?php

namespace CultuurNet\UDB3\Place\ReadModel\JSONLD;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\Serializer\SerializerInterface;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Facility;
use CultuurNet\UDB3\Iri\CallableIriGenerator;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Media\Serialization\MediaObjectSerializer;
use CultuurNet\UDB3\OfferLDProjectorTestBase;
use CultuurNet\UDB3\Place\Events\FacilitiesUpdated;
use CultuurNet\UDB3\Place\Events\LabelAdded;
use CultuurNet\UDB3\Place\Events\LabelDeleted;
use CultuurNet\UDB3\Place\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2Event;
use CultuurNet\UDB3\Place\Events\PlaceUpdatedFromUDB2;
use CultuurNet\UDB3\Place\ReadModel\JSONLD\PlaceLDProjector;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use PHPUnit_Framework_MockObject_MockObject;
use stdClass;

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

        $this->projector = new PlaceLDProjector(
            $this->documentRepository,
            $this->iriGenerator,
            $this->organizerService,
            $this->serializer
        );
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
            new Address('$street', '$postalCode', '$locality', '$country'),
            new Calendar('permanent')
        );

        $jsonLD = new stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $id;
        $jsonLD->{'@context'} = '/api/1.0/place.jsonld';
        $jsonLD->name = (object)[ 'nl' => 'some representative title' ];
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
        $jsonLD->created = $created;
        $jsonLD->modified = $created;

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
            new Address('$street', '$postalCode', '$locality', '$country'),
            new Calendar('permanent'),
            new Theme('123', 'theme label')
        );

        $jsonLD = new stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $id;
        $jsonLD->{'@context'} = '/api/1.0/place.jsonld';
        $jsonLD->name = (object)[ 'nl' => 'some representative title' ];
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
            ],
            (object)[
                'id' => '123',
                'label' => 'theme label',
                'domain' => 'theme',
            ]
        ];
        $jsonLD->created = $created;
        $jsonLD->modified = $created;

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
            new Address('$street', '$postalCode', '$locality', '$country'),
            new Calendar('permanent')
        );

        $jsonLD = new stdClass();
        $jsonLD->{'@id'} = 'http://example.com/entity/' . $id;
        $jsonLD->{'@context'} = '/api/1.0/place.jsonld';
        $jsonLD->name = 'some representative title';
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
        $jsonLD->created = $created;
        $jsonLD->modified = $created;
        $jsonLD->creator = '1 (Tester)';

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
     * @test
     */
    public function it_adds_an_image_property_when_cdbxml_has_a_photo()
    {
        $event = $this->placeImportedFromUDB2('place_with_image.cdbxml.xml');

        $body = $this->project($event, $event->getActorId());

        $this->assertEquals(
            '//media.uitdatabank.be/20141105/ed466c72-451f-4079-94d3-4ab2e0be7b15.jpg',
            $body->image
        );
    }

    /**
     * @test
     */
    public function it_imports_place_events_from_udb2()
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/event.xml'
        );
        $event = new PlaceImportedFromUDB2Event(
            '764066ab-826f-48c2-897d-a329ebce953f',
            $cdbXml,
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL'
        );

        $body = $this->project($event, $event->getActorId());

        $this->assertEquals('Invoerders Algemeen ', $body->publisher);
        $this->assertEquals('Vuur, vakmanschap en', $body->name->nl);
        $this->assertContains('764066ab-826f-48c2-897d-a329ebce953f', $body->{'@id'});
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
        $placeImportedFromUdb2 = $this->placeImportedFromUDB2('place_without_image.cdbxml.xml');
        $actorId = $placeImportedFromUdb2->getActorId();

        $cdbXml = file_get_contents(
            __DIR__ . '/place_with_image.cdbxml.xml'
        );
        $placeUpdatedFromUdb2 = new PlaceUpdatedFromUDB2(
            $actorId,
            $cdbXml,
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
        );
        
        $body = $this->project($placeUpdatedFromUdb2, $actorId);

        $this->assertEquals(
            '//media.uitdatabank.be/20141105/ed466c72-451f-4079-94d3-4ab2e0be7b15.jpg',
            $body->image
        );
    }

    /**
     * @test
     */
    public function it_updates_a_place_from_udb2_when_it_has_been_deleted_in_udb3()
    {
        $placeImportedFromUdb2 = $this->placeImportedFromUDB2('place_without_image.cdbxml.xml');
        $actorId = $placeImportedFromUdb2->getActorId();
        
        $placeDeleted = new PlaceDeleted($actorId);
        $this->project($placeDeleted, $actorId, null, null, false);

        $cdbXml = file_get_contents(
            __DIR__ . '/place_with_image.cdbxml.xml'
        );
        $placeUpdatedFromUdb2 = new PlaceUpdatedFromUDB2(
            $actorId,
            $cdbXml,
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
        );

        $body = $this->project($placeUpdatedFromUdb2, $actorId);

        $this->assertEquals(
            '//media.uitdatabank.be/20141105/ed466c72-451f-4079-94d3-4ab2e0be7b15.jpg',
            $body->image
        );
    }

    /**
     * @test
     * @dataProvider descriptionSamplesProvider
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
        $address = new Address('$newStreet', '$newPostalCode', '$newLocality', '$newCountry');
        $calendar = new Calendar('single', '2015-01-26T13:25:21+01:00', '2015-02-26T13:25:21+01:00');
        $theme = new Theme('123', 'theme label');
        $majorInfoUpdated = new MajorInfoUpdated($id, $title, $eventType, $address, $calendar, $theme);

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
          'addressCountry' => '$newCountry',
          'addressLocality' => '$newLocality',
          'postalCode' => '$newPostalCode',
          'streetAddress' => '$newStreet',
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

        $this->setExpectedException(DocumentGoneException::class);

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

        $labelDeleted = new LabelDeleted(
            'foo',
            new Label('label B')
        );

        $body = $this->project($labelDeleted, 'foo');

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
}
