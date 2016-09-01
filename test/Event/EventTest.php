<?php

namespace CultuurNet\UDB3\Event;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\Events\CollaborationDataAdded;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Event\Events\ImageAdded;
use CultuurNet\UDB3\Event\Events\ImageRemoved;
use CultuurNet\UDB3\Event\Events\LabelsMerged;
use CultuurNet\UDB3\Event\Events\LabelDeleted;
use CultuurNet\UDB3\EventXmlString;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\CollaborationData;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Media\MediaObject;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use CultuurNet\UDB3\Title;
use CultuurNet\UDB3\Translation;
use Guzzle\Tests\Http\DuplicateAggregatorTest;
use PHPUnit_Framework_TestCase;
use ValueObjects\Geography\Country;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\Url;

class EventTest extends AggregateRootScenarioTestCase
{
    const NS_CDBXML_3_2 = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL';
    const NS_CDBXML_3_3 = 'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL';

    /**
     * @inheritdoc
     */
    protected function getAggregateRootClass()
    {
        return Event::class;
    }

    /**
     * @var Event
     */
    protected $event;

    public function setUp()
    {
        parent::setUp();

        $this->event = Event::create(
            'foo',
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Location(
                UUID::generateAsString(),
                new StringLiteral('P-P-Partyzone'),
                new Address(
                    new Street('Kerkstraat 69'),
                    new PostalCode('3000'),
                    new Locality('Leuven'),
                    Country::fromNative('BE')
                )
            ),
            new Calendar('permanent', '', '')
        );
    }

    /**
     * @test
     */
    public function it_throws_an_error_when_creating_an_event_with_a_non_string_eventid()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Expected eventId to be a string, received integer'
        );

        $event = Event::create(
            101,
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Location(
                UUID::generateAsString(),
                new StringLiteral('P-P-Partyzone'),
                new Address(
                    new Street('Kerkstraat 69'),
                    new PostalCode('3000'),
                    new Locality('Leuven'),
                    Country::fromNative('BE')
                )
            ),
            new Calendar('permanent', '', '')
        );
    }

    /**
     * @test
     */
    public function it_can_be_tagged_with_multiple_labels()
    {
        $this->event->addLabel(new Label('foo'));

        $this->assertEquals(
            (new LabelCollection())->with(new Label('foo')),
            $this->event->getLabels()
        );

        $this->event->addLabel(new Label('bar'));

        $this->assertEquals(
            new LabelCollection(
                [
                    new Label('foo'),
                    new Label('bar')
                ]
            ),
            $this->event->getLabels()
        );
    }

    /**
     * @test
     */
    public function it_only_applies_the_same_tag_once()
    {
        $this->event->addLabel(new Label('foo'));
        $this->event->addLabel(new Label('foo'));

        $this->assertEquals(
            (new LabelCollection())->with(new Label('foo')),
            $this->event->getLabels()
        );
    }

    /**
     * @test
     */
    public function it_does_not_add_similar_labels_with_different_letter_casing()
    {
        $this->event->addLabel(new Label('Foo'));
        $this->event->addLabel(new Label('foo'));
        $this->event->addLabel(new Label('België'));
        $this->event->addLabel(new Label('BelgiË'));

        $expectedLabels = [
            new Label('Foo'),
            new Label('België')
        ];

        $this->assertEquals(
            new LabelCollection($expectedLabels),
            $this->event->getLabels()
        );
    }

    /**
     * @test
     */
    public function it_can_be_imported_from_udb2_cdbxml()
    {
        $cdbXml = $this->getSample('EventTest.cdbxml.xml');

        $event = Event::importFromUDB2(
            'someId',
            $cdbXml,
            self::NS_CDBXML_3_2
        );

        $expectedLabels = [
            new Label('kunst'),
            new Label('tentoonstelling'),
            new Label('brugge'),
            new Label('grafiek'),
            new Label('oud sint jan'),
            new Label('TRAEGHE GENUINE ARTS'),
            new Label('janine de conink'),
            new Label('brugge oktober'),
        ];

        $this->assertEquals(
            new LabelCollection($expectedLabels),
            $event->getLabels()
        );
    }

    /**
     * @test
     */
    public function it_can_be_created_from_cdbxml()
    {
        $cdbXml = $this->getSample('event_entryapi_valid_with_keywords.xml');

        $event = Event::createFromCdbXml(
            new StringLiteral('someId'),
            new EventXmlString($cdbXml),
            new StringLiteral(self::NS_CDBXML_3_3)
        );

        $expectedLabels = [
            new Label('polen'),
            new Label('slagwerk'),
        ];

        $this->assertEquals(
            new LabelCollection($expectedLabels),
            $event->getLabels()
        );
    }

    /**
     * @test
     */
    public function it_can_be_updated_from_cdbxml()
    {
        $cdbXml = $this->getSample('event_entryapi_valid_with_keywords.xml');

        $event = Event::createFromCdbXml(
            new StringLiteral('someId'),
            new EventXmlString($cdbXml),
            new StringLiteral(self::NS_CDBXML_3_3)
        );

        $cdbXmlEdited = $this->getSample('event_entryapi_valid_with_keywords_edited.xml');

        $event->updateFromCdbXml(
            new StringLiteral('someId'),
            new EventXmlString($cdbXmlEdited),
            new StringLiteral(self::NS_CDBXML_3_3)
        );

        $expectedLabels = [
            new Label('polen'),
            new Label('slagwerk'),
            new Label('test'),
            new Label('aangepast'),
        ];

        $this->assertEquals(
            new LabelCollection($expectedLabels),
            $event->getLabels()
        );
    }

    /**
     * @return array
     */
    public function unlabelDataProvider()
    {
        $label = new Label('foo');

        $id = '004aea08-e13d-48c9-b9eb-a18f20e6d44e';
        $ns = self::NS_CDBXML_3_3;
        $cdbXml = $this->getSample('event_004aea08-e13d-48c9-b9eb-a18f20e6d44e.xml');
        $cdbXmlWithFooKeyword = $this->getSample('event_004aea08-e13d-48c9-b9eb-a18f20e6d44e_additional_keyword.xml');

        $eventImportedFromUdb2 = new EventImportedFromUDB2(
            $id,
            $cdbXml,
            $ns
        );

        return [
            'label added by udb3' => [
                $id,
                $label,
                [
                    $eventImportedFromUdb2,
                    new LabelAdded(
                        $id,
                        $label
                    ),
                ]
            ],
            'label added by update from udb2' => [
                $id,
                $label,
                [
                    $eventImportedFromUdb2,
                    new EventUpdatedFromUDB2(
                        $id,
                        $cdbXmlWithFooKeyword,
                        $ns
                    ),
                ]
            ],
            'label merged through Entry API' => [
                $id,
                $label,
                [
                    $eventImportedFromUdb2,
                    new LabelsMerged(
                        new StringLiteral($id),
                        new LabelCollection(
                            [
                                $label
                            ]
                        )
                    ),
                ]
            ],
            'label with different casing' => [
                $id,
                $label,
                [
                    $eventImportedFromUdb2,
                    new LabelAdded(
                        $id,
                        new Label('fOO')
                    ),
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider unlabelDataProvider
     * @param string $id
     * @param Label $label
     * @param array $givens
     */
    public function it_can_be_unlabelled(
        $id,
        Label $label,
        array $givens
    ) {
        $this->scenario
            ->given($givens)
            ->when(
                function (Event $event) use ($label) {
                    $event->deleteLabel($label);
                }
            )
            ->then(
                [
                    new LabelDeleted($id, $label)
                ]
            );
    }

    /**
     * @return array
     */
    public function unlabelIgnoredDataProvider()
    {
        $label = new Label('foo');

        $id = '004aea08-e13d-48c9-b9eb-a18f20e6d44e';
        $ns = self::NS_CDBXML_3_3;
        $cdbXml = $this->getSample('event_004aea08-e13d-48c9-b9eb-a18f20e6d44e.xml');
        $cdbXmlWithFooKeyword = $this->getSample('event_004aea08-e13d-48c9-b9eb-a18f20e6d44e_additional_keyword.xml');

        $eventImportedFromUdb2 = new EventImportedFromUDB2(
            $id,
            $cdbXml,
            $ns
        );

        return [
            'label not present in imported udb2 cdbxml' => [
                $label,
                [
                    $eventImportedFromUdb2,
                ]
            ],
            'label previously removed by an update from udb2' => [
                $label,
                [
                    new EventImportedFromUDB2(
                        $id,
                        $cdbXmlWithFooKeyword,
                        $ns
                    ),
                    new EventUpdatedFromUDB2(
                        $id,
                        $cdbXml,
                        $ns
                    ),
                ]
            ],
            'label previously removed' => [
                $label,
                [
                    $eventImportedFromUdb2,
                    new LabelAdded(
                        $id,
                        $label
                    ),
                    new LabelDeleted(
                        $id,
                        $label
                    )
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider unlabelIgnoredDataProvider
     * @param Label $label
     * @param array $givens
     */
    public function it_silently_ignores_unlabel_request_if_label_is_not_present(
        Label $label,
        array $givens
    ) {
        $this->scenario
            ->given($givens)
            ->when(
                function (Event $event) use ($label) {
                    $event->deleteLabel($label);
                }
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_can_have_labels_merged()
    {
        $cdbXml = $this->getSample('event_entryapi_valid_with_keywords.xml');

        $event = Event::createFromCdbXml(
            new StringLiteral('someId'),
            new EventXmlString($cdbXml),
            new StringLiteral(self::NS_CDBXML_3_3)
        );

        $labels = new LabelCollection(
            [
                new Label('muziek', true),
                new Label('orkest', false),
            ]
        );

        $event->mergeLabels($labels);

        $expectedLabels = [
            new Label('polen'),
            new Label('slagwerk'),
            new Label('muziek'),
            new Label('orkest', false),
        ];

        $this->assertEquals(
            new LabelCollection($expectedLabels),
            $event->getLabels()
        );
    }

    /**
     * @test
     */
    public function it_requires_at_least_one_label_when_merging_labels()
    {
        $cdbXml = $this->getSample('event_entryapi_valid_with_keywords.xml');

        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Argument $labels should contain at least one label'
        );

        $event = Event::createFromCdbXml(
            new StringLiteral('someId'),
            new EventXmlString($cdbXml),
            new StringLiteral(
                self::NS_CDBXML_3_3
            )
        );

        $event->mergeLabels(new LabelCollection());
    }

    /**
     * @test
     */
    public function it_can_have_a_new_translation_applied()
    {
        $cdbXml = $this->getSample('event_entryapi_valid_with_keywords.xml');

        $event = Event::createFromCdbXml(
            new StringLiteral('someId'),
            new EventXmlString($cdbXml),
            new StringLiteral(self::NS_CDBXML_3_3)
        );

        $event->applyTranslation(
            new Language('fr'),
            new StringLiteral('Dizorkestra en concert'),
            new StringLiteral('Concert Dizôrkestra, un groupe qui.'),
            new StringLiteral('Concert Dizôrkestra, un groupe qui se montre inventif.')
        );

        $this->assertEquals(
            array(
                'fr' => new Translation(
                    new Language('fr'),
                    new StringLiteral('Dizorkestra en concert'),
                    new StringLiteral('Concert Dizôrkestra, un groupe qui.'),
                    new StringLiteral('Concert Dizôrkestra, un groupe qui se montre inventif.')
                ),
            ),
            $event->getTranslations()
        );
    }

    /**
     * @test
     */
    public function it_can_have_an_existing_translation_updated()
    {
        $cdbXml = $this->getSample('event_entryapi_valid_with_keywords.xml');

        $event = Event::createFromCdbXml(
            new StringLiteral('someId'),
            new EventXmlString($cdbXml),
            new StringLiteral(
                self::NS_CDBXML_3_3
            )
        );

        $event->applyTranslation(
            new Language('fr'),
            new StringLiteral('Dizorkestra en concert'),
            new StringLiteral('Concert Dizôrkestra, un groupe qui.'),
            new StringLiteral('Concert Dizôrkestra, un groupe qui se montre inventif.')
        );

        $this->assertEquals(
            array(
                'fr' => new Translation(
                    new Language('fr'),
                    new StringLiteral('Dizorkestra en concert'),
                    new StringLiteral('Concert Dizôrkestra, un groupe qui.'),
                    new StringLiteral('Concert Dizôrkestra, un groupe qui se montre inventif.')
                ),
            ),
            $event->getTranslations()
        );

        $event->applyTranslation(
            new Language('fr'),
            new StringLiteral('Nicorkestra en concert'),
            new StringLiteral('Concert Nicôrkestra, un groupe qui.'),
            new StringLiteral('Concert Nicôrkestra, un groupe qui se montre inventif.')
        );

        $this->assertEquals(
            array(
                'fr' => new Translation(
                    new Language('fr'),
                    new StringLiteral('Nicorkestra en concert'),
                    new StringLiteral('Concert Nicôrkestra, un groupe qui.'),
                    new StringLiteral('Concert Nicôrkestra, un groupe qui se montre inventif.')
                ),
            ),
            $event->getTranslations()
        );
    }

    /**
     * @test
     */
    public function it_can_have_a_translation_deleted()
    {
        $cdbXml = $this->getSample('event_entryapi_valid_with_keywords.xml');

        $event = Event::createFromCdbXml(
            new StringLiteral('someId'),
            new EventXmlString($cdbXml),
            new StringLiteral(self::NS_CDBXML_3_3)
        );

        $event->applyTranslation(
            new Language('fr'),
            new StringLiteral('Dizorkestra en concert'),
            new StringLiteral('Concert Dizôrkestra, un groupe qui.'),
            new StringLiteral('Concert Dizôrkestra, un groupe qui se montre inventif.')
        );

        $this->assertEquals(
            array(
                'fr' => new Translation(
                    new Language('fr'),
                    new StringLiteral('Dizorkestra en concert'),
                    new StringLiteral('Concert Dizôrkestra, un groupe qui.'),
                    new StringLiteral('Concert Dizôrkestra, un groupe qui se montre inventif.')
                ),
            ),
            $event->getTranslations()
        );

        $event->deleteTranslation(
            new Language('fr')
        );

        $this->assertEquals(
            array(),
            $event->getTranslations()
        );
    }

    /**
     * @test
     */
    public function it_can_have_a_translation_deleted_when_no_translation_exists()
    {
        $cdbXml = $this->getSample('event_entryapi_valid_with_keywords.xml');

        $event = Event::createFromCdbXml(
            new StringLiteral('someId'),
            new EventXmlString($cdbXml),
            new StringLiteral(self::NS_CDBXML_3_3)
        );

        $this->assertEquals(
            array(),
            $event->getTranslations()
        );

        $event->deleteTranslation(
            new Language('fr')
        );

        $this->assertEquals(
            array(),
            $event->getTranslations()
        );
    }

    /**
     * @test
     */
    public function it_can_have_collaboration_data_added()
    {
        $cdbXml = file_get_contents(__DIR__ . '/samples/event_entryapi_valid_with_keywords.xml');
        $french = new Language('fr');

        $collaborationData = CollaborationData::deserialize(
            [
                'subBrand' => 'sub brand',
                'title' => 'title',
                'text' => 'description EN',
                'copyright' => 'copyright',
                'keyword' => 'Lorem',
                'image' => '/image.en.png',
                'article' => 'Ipsum',
                'link' => 'http://google.com',
            ]
        );

        $secondCollaborationData = CollaborationData::deserialize(
            [
                'subBrand' => 'sub brand',
                'title' => 'title 2',
                'text' => 'description EN',
                'copyright' => 'copyright',
                'keyword' => 'Lorem',
                'image' => '/image.en.png',
                'article' => 'Ipsum',
                'link' => 'http://google.com',
            ]
        );

        $this->scenario
            ->withAggregateId('someId')
            ->given(
                [
                    new EventCreatedFromCdbXml(
                        new StringLiteral('someId'),
                        new EventXmlString($cdbXml),
                        new StringLiteral('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL')
                    )
                ]
            )
            ->when(
                function (Event $event) use ($french, $collaborationData, $secondCollaborationData) {
                    $event->addCollaborationData(
                        $french,
                        $collaborationData
                    );

                    $event->addCollaborationData(
                        $french,
                        $secondCollaborationData
                    );
                }
            )
            ->then(
                [
                    new CollaborationDataAdded(
                        new StringLiteral('someId'),
                        $french,
                        $collaborationData
                    ),
                    new CollaborationDataAdded(
                        new StringLiteral('someId'),
                        $french,
                        $secondCollaborationData
                    ),
                ]
            );
    }

    /**
     * @test
     */
    public function it_does_not_add_collaboration_data_twice_for_the_same_language()
    {
        $cdbXml = file_get_contents(__DIR__ . '/samples/event_entryapi_valid_with_keywords.xml');

        $eventId = new StringLiteral('someId');

        $french = new Language('fr');

        $collaborationData = CollaborationData::deserialize(
            [
                'subBrand' => 'sub brand',
                'title' => 'title',
                'text' => 'description EN',
                'copyright' => 'copyright',
                'keyword' => 'Lorem',
                'image' => '/image.en.png',
                'article' => 'Ipsum',
                'link' => 'http://google.com',
            ]
        );

        $this->scenario
            ->withAggregateId('someId')
            ->given(
                [
                    new EventCreatedFromCdbXml(
                        $eventId,
                        new EventXmlString($cdbXml),
                        new StringLiteral('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL')
                    ),
                    new CollaborationDataAdded(
                        $eventId,
                        $french,
                        $collaborationData
                    ),
                ]
            )
            ->when(
                function (Event $event) use ($french, $collaborationData) {
                    $event->addCollaborationData(
                        $french,
                        $collaborationData
                    );
                }
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_can_add_collaboration_data_twice_for_different_languages()
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/samples/event_entryapi_valid_with_keywords.xml'
        );

        $french = new Language('fr');
        $english = new Language('en');

        $eventId = new StringLiteral('someId');

        $collaborationData = CollaborationData::deserialize(
            [
                'subBrand' => 'sub brand',
                'title' => 'title',
                'text' => 'description EN',
                'copyright' => 'copyright',
                'keyword' => 'Lorem',
                'image' => '/image.en.png',
                'article' => 'Ipsum',
                'link' => 'http://google.com',
            ]
        );

        $this->scenario
            ->withAggregateId('someId')
            ->given(
                [
                    new EventCreatedFromCdbXml(
                        $eventId,
                        new EventXmlString($cdbXml),
                        new StringLiteral('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL')
                    ),
                    new CollaborationDataAdded(
                        $eventId,
                        $french,
                        $collaborationData
                    ),
                ]
            )
            ->when(
                function (Event $event) use ($english, $collaborationData) {
                    $event->addCollaborationData(
                        $english,
                        $collaborationData
                    );
                }
            )
            ->then(
                [
                    new CollaborationDataAdded(
                        $eventId,
                        $english,
                        $collaborationData
                    ),
                ]
            );
    }

    /**
     * @test
     */
    public function it_updates_collaboration_data_based_on_combination_of_language_and_subbrand()
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/samples/event_entryapi_valid_with_keywords.xml'
        );

        $french = new Language('fr');

        $eventId = new StringLiteral('someId');

        $collaborationData = CollaborationData::deserialize(
            [
                'subBrand' => 'sub brand',
                'title' => 'title',
                'text' => 'description EN',
                'copyright' => 'copyright',
                'keyword' => 'Lorem',
                'image' => '/image.en.png',
                'article' => 'Ipsum',
                'link' => 'http://google.com',
            ]
        );

        // Update with all data different, except for sub brand.
        $updatedCollaborationData = $collaborationData
            ->withTitle(new StringLiteral('title bis'))
            ->withText(new StringLiteral('description EN bis'))
            ->withCopyright(new StringLiteral('copyright bis'))
            ->withKeyword(new StringLiteral('Lorem bis'))
            ->withImage(new StringLiteral('/image.en.bis.png'))
            ->withArticle(new StringLiteral('Ipsum bis'))
            ->withLink(Url::fromNative('http://google.bis.com'));

        $otherSubBrandCollaborationData = CollaborationData::deserialize(
            [
                'subBrand' => 'sub brand bis',
                'title' => 'title',
                'text' => 'description EN',
                'copyright' => 'copyright',
                'keyword' => 'Lorem',
                'image' => '/image.en.png',
                'article' => 'Ipsum',
                'link' => 'http://google.com',
            ]
        );

        $this->scenario
            ->withAggregateId('someId')
            ->given(
                [
                    new EventCreatedFromCdbXml(
                        $eventId,
                        new EventXmlString($cdbXml),
                        new StringLiteral('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL')
                    ),
                    new CollaborationDataAdded(
                        $eventId,
                        $french,
                        $collaborationData
                    ),
                    new CollaborationDataAdded(
                        $eventId,
                        $french,
                        $otherSubBrandCollaborationData
                    ),
                    new CollaborationDataAdded(
                        $eventId,
                        $french,
                        $updatedCollaborationData
                    ),
                ]
            )
            ->when(
                function (Event $event) use ($french, $collaborationData) {
                    $event->addCollaborationData(
                        $french,
                        $collaborationData
                    );
                }
            )
            ->then(
                [
                    new CollaborationDataAdded(
                        $eventId,
                        $french,
                        $collaborationData
                    ),
                ]
            );
    }

    /**
     * @test
     */
    public function it_should_not_add_duplicate_images()
    {
        $image = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new StringLiteral('sexy ladies without clothes'),
            new StringLiteral('Bart Ramakers'),
            Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png')
        );

        $cdbXml = file_get_contents(
            __DIR__ . '/samples/event_entryapi_valid_with_keywords.xml'
        );

        $this->scenario
            ->withAggregateId('foo')
            ->given(
                [
                    new EventCreatedFromCdbXml(
                        new StringLiteral('foo'),
                        new EventXmlString($cdbXml),
                        new StringLiteral('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL')
                    ),
                    new ImageAdded(
                        'foo',
                        $image
                    ),
                ]
            )
            ->when(
                function (Event $event) use ($image) {
                    $event->addImage(
                        $image
                    );
                }
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_removes_images()
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/samples/event_entryapi_valid_with_keywords.xml'
        );

        $image = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new StringLiteral('sexy ladies without clothes'),
            new StringLiteral('Bart Ramakers'),
            Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png')
        );

        $this->scenario
            ->withAggregateId('foo')
            ->given(
                [
                    new EventCreatedFromCdbXml(
                        new StringLiteral('foo'),
                        new EventXmlString($cdbXml),
                        new StringLiteral('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL')
                    ),
                    new ImageAdded(
                        'foo',
                        $image
                    ),
                ]
            )
            ->when(
                function (Event $event) use ($image) {
                    $event->removeImage(
                        $image
                    );
                }
            )
            ->then(
                [
                    new ImageRemoved(
                        'foo',
                        $image
                    ),
                ]
            );
    }

    /**
     * @test
     */
    public function it_silently_ignores_an_image_removal_request_when_image_is_not_present()
    {
        $cdbXml = file_get_contents(
            __DIR__ . '/samples/event_entryapi_valid_with_keywords.xml'
        );

        $image = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new StringLiteral('sexy ladies without clothes'),
            new StringLiteral('Bart Ramakers'),
            Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png')
        );

        $this->scenario
            ->withAggregateId('foo')
            ->given(
                [
                    new EventCreatedFromCdbXml(
                        new StringLiteral('foo'),
                        new EventXmlString($cdbXml),
                        new StringLiteral('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL')
                    ),
                ]
            )
            ->when(
                function (Event $event) use ($image) {
                    $event->removeImage(
                        $image
                    );
                }
            )
            ->then([]);
    }

    /**
     * @param string $file
     * @return string
     */
    protected function getSample($file)
    {
        return file_get_contents(
            __DIR__ . '/samples/' . $file
        );
    }
}
