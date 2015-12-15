<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventWasLabelled;
use CultuurNet\UDB3\Event\Events\LabelsMerged;
use CultuurNet\UDB3\Event\Events\Unlabelled;
use CultuurNet\UDB3\EventXmlString;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Title;
use CultuurNet\UDB3\Translation;
use PHPUnit_Framework_TestCase;
use ValueObjects\String\String;

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
                'LOCATION-ABC-123',
                '$name',
                '$country',
                '$locality',
                '$postalcode',
                '$street'
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
                'LOCATION-ABC-123',
                '$name',
                '$country',
                '$locality',
                '$postalcode',
                '$street'
            ),
            new Calendar('permanent', '', '')
        );
    }

    /**
     * @test
     */
    public function it_can_be_tagged_with_multiple_labels()
    {
        $this->event->label(new Label('foo'));

        $this->assertEquals(
            (new LabelCollection())->with(new Label('foo')),
            $this->event->getLabels()
        );

        $this->event->label(new Label('bar'));

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
        $this->event->label(new Label('foo'));
        $this->event->label(new Label('foo'));

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
        $this->event->label(new Label('Foo'));
        $this->event->label(new Label('foo'));
        $this->event->label(new Label('België'));
        $this->event->label(new Label('BelgiË'));

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
            new String('someId'),
            new EventXmlString($cdbXml),
            new String(self::NS_CDBXML_3_3)
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
            new String('someId'),
            new EventXmlString($cdbXml),
            new String(self::NS_CDBXML_3_3)
        );

        $cdbXmlEdited = $this->getSample('event_entryapi_valid_with_keywords_edited.xml');

        $event->updateFromCdbXml(
            new String('someId'),
            new EventXmlString($cdbXmlEdited),
            new String(self::NS_CDBXML_3_3)
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
                    new EventWasLabelled(
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
                        new String($id),
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
                    new EventWasLabelled(
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
                    $event->unlabel($label);
                }
            )
            ->then(
                [
                    new Unlabelled($id, $label)
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
                    new EventWasLabelled(
                        $id,
                        $label
                    ),
                    new Unlabelled(
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
                    $event->unlabel($label);
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
            new String('someId'),
            new EventXmlString($cdbXml),
            new String(self::NS_CDBXML_3_3)
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
            new String('someId'),
            new EventXmlString($cdbXml),
            new String(
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
            new String('someId'),
            new EventXmlString($cdbXml),
            new String(self::NS_CDBXML_3_3)
        );

        $event->applyTranslation(
            new Language('fr'),
            new String('Dizorkestra en concert'),
            new String('Concert Dizôrkestra, un groupe qui.'),
            new String('Concert Dizôrkestra, un groupe qui se montre inventif.')
        );

        $this->assertEquals(
            array(
                'fr' => new Translation(
                    new Language('fr'),
                    new String('Dizorkestra en concert'),
                    new String('Concert Dizôrkestra, un groupe qui.'),
                    new String('Concert Dizôrkestra, un groupe qui se montre inventif.')
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
            new String('someId'),
            new EventXmlString($cdbXml),
            new String(
                self::NS_CDBXML_3_3
            )
        );

        $event->applyTranslation(
            new Language('fr'),
            new String('Dizorkestra en concert'),
            new String('Concert Dizôrkestra, un groupe qui.'),
            new String('Concert Dizôrkestra, un groupe qui se montre inventif.')
        );

        $this->assertEquals(
            array(
                'fr' => new Translation(
                    new Language('fr'),
                    new String('Dizorkestra en concert'),
                    new String('Concert Dizôrkestra, un groupe qui.'),
                    new String('Concert Dizôrkestra, un groupe qui se montre inventif.')
                ),
            ),
            $event->getTranslations()
        );

        $event->applyTranslation(
            new Language('fr'),
            new String('Nicorkestra en concert'),
            new String('Concert Nicôrkestra, un groupe qui.'),
            new String('Concert Nicôrkestra, un groupe qui se montre inventif.')
        );

        $this->assertEquals(
            array(
                'fr' => new Translation(
                    new Language('fr'),
                    new String('Nicorkestra en concert'),
                    new String('Concert Nicôrkestra, un groupe qui.'),
                    new String('Concert Nicôrkestra, un groupe qui se montre inventif.')
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
            new String('someId'),
            new EventXmlString($cdbXml),
            new String(self::NS_CDBXML_3_3)
        );

        $event->applyTranslation(
            new Language('fr'),
            new String('Dizorkestra en concert'),
            new String('Concert Dizôrkestra, un groupe qui.'),
            new String('Concert Dizôrkestra, un groupe qui se montre inventif.')
        );

        $this->assertEquals(
            array(
                'fr' => new Translation(
                    new Language('fr'),
                    new String('Dizorkestra en concert'),
                    new String('Concert Dizôrkestra, un groupe qui.'),
                    new String('Concert Dizôrkestra, un groupe qui se montre inventif.')
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
            new String('someId'),
            new EventXmlString($cdbXml),
            new String(self::NS_CDBXML_3_3)
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
