<?php

namespace CultuurNet\UDB3\Event;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Event\Events\ImageAdded;
use CultuurNet\UDB3\Event\Events\ImageRemoved;
use CultuurNet\UDB3\Event\Events\LabelsMerged;
use CultuurNet\UDB3\Event\Events\LabelRemoved;
use CultuurNet\UDB3\EventXmlString;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location\Location;
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
            new Calendar(CalendarType::PERMANENT())
        );
    }

    private function getCreationEvent()
    {
        return new EventCreated(
            'd2b41f1d-598c-46af-a3a5-10e373faa6fe',
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
            new Calendar(CalendarType::PERMANENT())
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
            new Calendar(CalendarType::PERMANENT())
        );
    }

    /**
     * @test
     */
    public function it_can_be_tagged_with_multiple_labels()
    {
        $this->scenario
            ->given([
                $this->getCreationEvent()
            ])
            ->when(
                function (Event $event) {
                    $event->addLabel(new Label('foo'));
                    $event->addLabel(new Label('bar'));
                }
            )
            ->then([
                new LabelAdded('d2b41f1d-598c-46af-a3a5-10e373faa6fe', new Label('foo')),
                new LabelAdded('d2b41f1d-598c-46af-a3a5-10e373faa6fe', new Label('bar')),
            ]);
    }

    /**
     * @test
     */
    public function it_only_applies_the_same_tag_once()
    {
        $this->scenario
            ->given([
                $this->getCreationEvent()
            ])
            ->when(
                function (Event $event) {
                    $event->addLabel(new Label('foo'));
                    $event->addLabel(new Label('foo'));
                }
            )
            ->then([
                new LabelAdded('d2b41f1d-598c-46af-a3a5-10e373faa6fe', new Label('foo')),
            ]);
    }

    /**
     * @test
     */
    public function it_does_not_add_similar_labels_with_different_letter_casing()
    {
        $this->scenario
            ->given([
                $this->getCreationEvent()
            ])
            ->when(
                function (Event $event) {
                    $event->addLabel(new Label('Foo'));
                    $event->addLabel(new Label('foo'));
                    $event->addLabel(new Label('België'));
                    $event->addLabel(new Label('BelgiË'));
                }
            )
            ->then([
                new LabelAdded('d2b41f1d-598c-46af-a3a5-10e373faa6fe', new Label('Foo')),
                new LabelAdded('d2b41f1d-598c-46af-a3a5-10e373faa6fe', new Label('België')),
            ]);
    }

    /**
     * @test
     */
    public function it_can_be_imported_from_udb2_cdbxml_without_any_labels()
    {
        $xmlData = $this->getSample('EventTest.cdbxml.xml');
        $eventId = 'a2d50a8d-5b83-4c8b-84e6-e9c0bacbb1a3';
        $xmlNamespace = self::NS_CDBXML_3_2;

        $this->scenario
            ->given([
                new EventImportedFromUDB2($eventId, $xmlData, $xmlNamespace)
            ])
            ->when(
                function (Event $event) {
                    $event->addLabel(new Label('kunst'));
                    $event->addLabel(new Label('tentoonstelling'));
                    $event->addLabel(new Label('brugge'));
                    $event->addLabel(new Label('grafiek'));
                    $event->addLabel(new Label('oud sint jan'));
                    $event->addLabel(new Label('TRAEGHE GENUINE ARTS'));
                    $event->addLabel(new Label('janine de conink'));
                    $event->addLabel(new Label('brugge oktober'));
                }
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_can_be_created_from_cdbxml()
    {
        $cdbXml = $this->getSample('event_entryapi_valid_with_keywords.xml');
        $eventId = new StringLiteral('someId');
        $xmlData = new EventXmlString($cdbXml);
        $xmlNamespace = new StringLiteral(self::NS_CDBXML_3_3);

        $this->scenario
            ->given([
                new EventCreatedFromCdbXml($eventId, $xmlData, $xmlNamespace)
            ])
            ->when(
                function (Event $event) {
                    $event->addLabel(new Label('polen'));
                    $event->addLabel(new Label('slagwerk'));
                }
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_can_be_updated_from_cdbxml()
    {
        $cdbXmlEdited = $this->getSample('event_entryapi_valid_with_keywords_edited.xml');

        $cdbXml = $this->getSample('event_entryapi_valid_with_keywords.xml');
        $eventId = new StringLiteral('someId');
        $xmlData = new EventXmlString($cdbXml);
        $editedxmlData = new EventXmlString($cdbXmlEdited);
        $xmlNamespace = new StringLiteral(self::NS_CDBXML_3_3);

        $this->scenario
            ->given([
                new EventCreatedFromCdbXml($eventId, $xmlData, $xmlNamespace)
            ])
            ->when(
                function (Event $event) use ($eventId, $editedxmlData, $xmlNamespace) {
                    $event->updateFromCdbXml($eventId, $editedxmlData, $xmlNamespace);

                    $event->addLabel(new Label('polen'));
                    $event->addLabel(new Label('slagwerk'));
                    $event->addLabel(new Label('test'));
                    $event->addLabel(new Label('aangepast'));
                }
            )
            ->then([
                new EventUpdatedFromCdbXml($eventId, $editedxmlData, $xmlNamespace)
            ]);
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
                    $event->removeLabel($label);
                }
            )
            ->then(
                [
                    new LabelRemoved($id, $label)
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
                    new LabelRemoved(
                        $id,
                        $label
                    ),
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
                    $event->removeLabel($label);
                }
            )
            ->then([]);
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
