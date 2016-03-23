<?php

namespace CultuurNet\UDB3\Offer\ReadModel\JSONLD;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\InMemoryDocumentRepository;
use CultuurNet\UDB3\Iri\CallableIriGenerator;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Item\Events\DescriptionTranslated;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use CultuurNet\UDB3\Media\Serialization\MediaObjectSerializer;
use CultuurNet\UDB3\Offer\Item\Events\ImageAdded;
use CultuurNet\UDB3\Offer\Item\Events\ImageRemoved;
use CultuurNet\UDB3\Offer\Item\Events\LabelAdded;
use CultuurNet\UDB3\Offer\Item\Events\LabelDeleted;
use CultuurNet\UDB3\Offer\Item\Events\MainImageSelected;
use CultuurNet\UDB3\Offer\Item\Events\TitleTranslated;
use CultuurNet\UDB3\Offer\Item\ReadModel\JSONLD\ItemLDProjector;
use CultuurNet\UDB3\OrganizerService;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use PHPUnit_Framework_MockObject_MockObject;
use stdClass;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;
use ValueObjects\Web\Url;

class OfferLDProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryDocumentRepository
     */
    protected $documentRepository;

    /**
     * @var ItemLDProjector
     */
    protected $projector;

    /**
     * @var IriGeneratorInterface
     */
    private $iriGenerator;

    /**
     * @var OrganizerService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $organizerService;

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
        parent::__construct($name, $data, $dataName, 'CultuurNet\\UDB3\\Offer\\Item');
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->documentRepository = new InMemoryDocumentRepository();

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

        $this->serializer = new MediaObjectSerializer($this->iriGenerator);

        $this->projector = new ItemLDProjector(
            $this->documentRepository,
            $this->iriGenerator,
            $this->organizerService,
            $this->serializer
        );
    }

    /**
     * @param object $event
     * @param string $entityId
     * @param Metadata|null $metadata
     * @param DateTime $dateTime
     * @return \stdClass
     */
    protected function project(
        $event,
        $entityId,
        Metadata $metadata = null,
        DateTime $dateTime = null
    ) {
        if (null === $metadata) {
            $metadata = new Metadata();
        }

        if (null === $dateTime) {
            $dateTime = DateTime::now();
        }

        $this->projector->handle(
            new DomainMessage(
                $entityId,
                1,
                $metadata,
                $event,
                $dateTime
            )
        );

        return $this->getBody($entityId);
    }

    /**
     * @param string $id
     * @return \stdClass
     */
    protected function getBody($id)
    {
        $document = $this->documentRepository->get($id);
        return $document->getBody();
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
    public function it_projects_the_translation_of_the_title()
    {
        $titleTranslated = new TitleTranslated(
            'foo',
            new Language('en'),
            new String('English title')
        );

        $initialDocument = new JsonDocument(
            'foo',
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

        $body = $this->project($titleTranslated, 'foo');

        $this->assertEquals(
            (object)[
                'name' => (object)[
                    'nl'=> 'Titel',
                    'en' => 'English title'
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
    public function it_projects_the_translation_of_the_description()
    {
        $descriptionTranslated = new DescriptionTranslated(
            'foo',
            new Language('en'),
            new String('English description')
        );

        $initialDocument = new JsonDocument(
            'foo',
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

        $body = $this->project($descriptionTranslated, 'foo');

        $this->assertEquals(
            (object)[
                'name' => (object)[
                    'nl'=> 'Titel',
                ],
                'description' => (object)[
                    'nl' => 'Omschrijving',
                    'en' => 'English description',
                ],
            ],
            $body
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
        $initialDocument = new JsonDocument(
            $eventId,
            json_encode([
                'image' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
            ])
        );

        $this->documentRepository->save($initialDocument);
        $imageAddedEvent = new ImageAdded($eventId, $image);
        $eventBody = $this->project($imageAddedEvent, $eventId);

        $this->assertEquals(
            $expectedMediaObjects,
            $eventBody->mediaObject
        );
    }

    public function mediaObjectDataProvider()
    {
        $eventId = 'event-1';

        $initialJsonStructure = [
            'image' => 'http://foo.bar/media/de305d54-ddde-eddd-adb2-eb6b9e546014.png',
        ];

        $initialJsonStructureWithMedia = $initialJsonStructure + [
                'mediaObject' => [
                    (object) [
                        '@id' => 'http://example.com/entity/de305d54-ddde-eddd-adb2-eb6b9e546014',
                        '@type' => 'schema:ImageObject',
                        'contentUrl' => 'http://foo.bar/media/de305d54-ddde-eddd-adb2-eb6b9e546014.png',
                        'thumbnailUrl' => 'http://foo.bar/media/de305d54-ddde-eddd-adb2-eb6b9e546014.png',
                        'description' => 'my best pokerface',
                        'copyrightHolder' => 'Hans Langucci'
                    ],
                    (object) [
                        '@id' => 'http://example.com/entity/de305d54-75b4-431b-adb2-eb6b9e546014',
                        '@type' => 'schema:ImageObject',
                        'contentUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                        'thumbnailUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                        'description' => 'sexy ladies without clothes',
                        'copyrightHolder' => 'Bart Ramakers'
                    ]
                ]
            ];

        $image1 = new Image(
            new UUID('de305d54-ddde-eddd-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new String('my best pokerface'),
            new String('Hans Langucci'),
            Url::fromNative(
                'http://foo.bar/media/de305d54-ddde-eddd-adb2-eb6b9e546014.png'
            )
        );

        $image2 = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new String('sexy ladies without clothes'),
            new String('Bart Ramakers'),
            Url::fromNative(
                'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png'
            )
        );

        $expectedWithoutLastImage = (object) [
            'image' => 'http://foo.bar/media/de305d54-ddde-eddd-adb2-eb6b9e546014.png',
            'mediaObject' => [
                (object) [
                    '@id' => 'http://example.com/entity/de305d54-ddde-eddd-adb2-eb6b9e546014',
                    '@type' => 'schema:ImageObject',
                    'contentUrl' => 'http://foo.bar/media/de305d54-ddde-eddd-adb2-eb6b9e546014.png',
                    'thumbnailUrl' => 'http://foo.bar/media/de305d54-ddde-eddd-adb2-eb6b9e546014.png',
                    'description' => 'my best pokerface',
                    'copyrightHolder' => 'Hans Langucci'
                ]
            ]
        ];

        $expectedWithoutFirstImage = (object) [
            'image' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
            'mediaObject' => [
                (object) [
                    '@id' => 'http://example.com/entity/de305d54-75b4-431b-adb2-eb6b9e546014',
                    '@type' => 'schema:ImageObject',
                    'contentUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                    'thumbnailUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                    'description' => 'sexy ladies without clothes',
                    'copyrightHolder' => 'Bart Ramakers'
                ]
            ]
        ];


        return [
            'document with 2 images, last image gets removed' => [
                new JsonDocument(
                    $eventId,
                    json_encode((object) $initialJsonStructureWithMedia)
                ),
                $image2,
                $expectedWithoutLastImage,
            ],
            'document with 2 images, first image gets removed' => [
                new JsonDocument(
                    $eventId,
                    json_encode((object) $initialJsonStructureWithMedia)
                ),
                $image1,
                $expectedWithoutFirstImage,
            ],
            'document without media' => [
                new JsonDocument(
                    $eventId,
                    json_encode((object) $initialJsonStructure)
                ),
                $image1,
                (object) $initialJsonStructure,
            ]
        ];
    }

    /**
     * @test
     * @dataProvider mediaObjectDataProvider
     */
    public function it_should_remove_the_media_object_of_an_image(JsonDocument $initialDocument, Image $image, $expectedProjection)
    {
        $this->documentRepository->save($initialDocument);
        $imageRemovedEvent = new ImageRemoved($initialDocument->getId(), $image);
        $eventBody = $this->project($imageRemovedEvent, $initialDocument->getId());

        $this->assertEquals(
            $expectedProjection,
            $eventBody
        );
    }

    /**
     * @test
     */
    public function it_should_destroy_the_media_object_attribute_when_no_media_objects_are_left_after_removing_an_image()
    {
        $eventId = 'event-1';
        $image = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new String('sexy ladies without clothes'),
            new String('Bart Ramakers'),
            Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png')
        );
        $initialDocument = new JsonDocument(
            $eventId,
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

        $this->documentRepository->save($initialDocument);
        $imageRemovedEvent = new ImageRemoved($eventId, $image);
        $eventBody = $this->project($imageRemovedEvent, $eventId);

        $this->assertObjectNotHasAttribute('mediaObject', $eventBody);
    }

    /**
     * @test
     */
    public function it_should_unset_the_main_image_when_its_media_object_is_removed()
    {
        $eventId = 'event-1';
        $image = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new String('sexy ladies without clothes'),
            new String('Bart Ramakers'),
            Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png')
        );
        $initialDocument = new JsonDocument(
            $eventId,
            json_encode([
                'image' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
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

        $this->documentRepository->save($initialDocument);
        $imageRemovedEvent = new ImageRemoved($eventId, $image);
        $eventBody = $this->project($imageRemovedEvent, $eventId);

        $this->assertObjectNotHasAttribute('image', $eventBody);
    }

    /**
     * @test
     */
    public function it_should_make_an_image_main_when_added_to_an_item_without_existing_ones()
    {
        $eventId = 'event-1';
        $image = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new String('sexy ladies without clothes'),
            new String('Bart Ramakers'),
            Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png')
        );
        $initialDocument = new JsonDocument(
            $eventId,
            json_encode([
                'pro' => 'jection'
            ])
        );

        $this->documentRepository->save($initialDocument);
        $imageAddedEvent = new ImageAdded($eventId, $image);
        $eventBody = $this->project($imageAddedEvent, $eventId);

        $this->assertEquals(
            'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
            $eventBody->image
        );
    }

    /**
     * @test
     */
    public function it_should_make_the_oldest_image_main_when_deleting_the_current_main_image()
    {
        $eventId = 'event-1';
        $image = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new String('sexy ladies without clothes'),
            new String('Bart Ramakers'),
            Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png')
        );
        $initialDocument = new JsonDocument(
            $eventId,
            json_encode([
                'image' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                'mediaObject' => [
                    [
                        '@id' => 'http://example.com/entity/de305d54-75b4-431b-adb2-eb6b9e546014',
                        '@type' => 'schema:ImageObject',
                        'contentUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                        'thumbnailUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                        'description' => 'sexy ladies without clothes',
                        'copyrightHolder' => 'Bart Ramakers'
                    ],
                    [
                        '@id' => 'http://example.com/entity/5ae74e68-20a3-4cb1-b255-8e405aa01ab9',
                        '@type' => 'schema:ImageObject',
                        'contentUrl' => 'http://foo.bar/media/5ae74e68-20a3-4cb1-b255-8e405aa01ab9.png',
                        'thumbnailUrl' => 'http://foo.bar/media/5ae74e68-20a3-4cb1-b255-8e405aa01ab9.png',
                        'description' => 'funny giphy image',
                        'copyrightHolder' => 'Bart Ramakers'
                    ]
                ]
            ])
        );

        $this->documentRepository->save($initialDocument);
        $imageRemovedEvent = new ImageRemoved($eventId, $image);
        $eventBody = $this->project($imageRemovedEvent, $eventId);

        $this->assertEquals(
            'http://foo.bar/media/5ae74e68-20a3-4cb1-b255-8e405aa01ab9.png',
            $eventBody->image
        );
    }

    /**
     * @test
     */
    public function it_should_set_the_image_property_when_selecting_a_main_image()
    {
        $eventId = 'event-1';
        $selectedMainImage = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new String('sexy ladies without clothes'),
            new String('Bart Ramakers'),
            Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png')
        );
        $initialDocument = new JsonDocument(
            $eventId,
            json_encode([
                'image' => 'http://foo.bar/media/5ae74e68-20a3-4cb1-b255-8e405aa01ab9.png',
                'mediaObject' => [
                    [
                        '@id' => 'http://example.com/entity/de305d54-75b4-431b-adb2-eb6b9e546014',
                        '@type' => 'schema:ImageObject',
                        'contentUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                        'thumbnailUrl' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
                        'description' => 'sexy ladies without clothes',
                        'copyrightHolder' => 'Bart Ramakers'
                    ],
                    [
                        '@id' => 'http://example.com/entity/5ae74e68-20a3-4cb1-b255-8e405aa01ab9',
                        '@type' => 'schema:ImageObject',
                        'contentUrl' => 'http://foo.bar/media/5ae74e68-20a3-4cb1-b255-8e405aa01ab9.png',
                        'thumbnailUrl' => 'http://foo.bar/media/5ae74e68-20a3-4cb1-b255-8e405aa01ab9.png',
                        'description' => 'funny giphy image',
                        'copyrightHolder' => 'Bart Ramakers'
                    ]
                ]
            ])
        );

        $this->documentRepository->save($initialDocument);
        $mainImageSelecetd = new MainImageSelected($eventId, $selectedMainImage);
        $eventBody = $this->project($mainImageSelecetd, $eventId);

        $this->assertEquals(
            'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png',
            $eventBody->image
        );
    }
}
