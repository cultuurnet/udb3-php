<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\EventXmlString;
use CultuurNet\UDB3SilexEntryAPI\KeywordsVisiblesPair;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Title;
use CultuurNet\UDB3\Translation;
use CultuurNet\UDB3\TranslationsString;
use PHPUnit_Framework_TestCase;
use ValueObjects\String\String;

class EventTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Event
     */
    protected $event;

    public function setUp()
    {
        $this->event = Event::create(
            'foo',
            new Title('some representative title'),
            new EventType('0.50.4.0.0', 'concert'),
            new Location('LOCATION-ABC-123', '$name', '$country', '$locality', '$postalcode', '$street'),
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
    public function it_unlabels_in_a_case_insensitive_way()
    {
        $this->event->label(new Label('foo'));

        $this->assertEquals(
            new LabelCollection(
                [
                    new Label('foo')
                ]
            ),
            $this->event->getLabels()
        );

        $this->event->unlabel(new Label('Foo'));

        $this->assertEquals(
            new LabelCollection(),
            $this->event->getLabels()
        );
    }

    /**
     * @test
     */
    public function it_can_be_imported_from_udb2_cdbxml()
    {
        $cdbXml = file_get_contents(__DIR__ . '/samples/EventTest.cdbxml.xml');
        $event = Event::importFromUDB2(
            'someId',
            $cdbXml,
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
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
        $cdbXml = file_get_contents(__DIR__ . '/samples/event_entryapi_valid_with_keywords.xml');
        $event = Event::createFromCdbXml(
            new String('someId'),
            new EventXmlString($cdbXml),
            new String('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL')
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
        $cdbXml = file_get_contents(__DIR__ . '/samples/event_entryapi_valid_with_keywords.xml');
        $event = Event::createFromCdbXml(
            new String('someId'),
            new EventXmlString($cdbXml),
            new String('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL')
        );

        $cdbXmlEdited = file_get_contents(__DIR__ . '/samples/event_entryapi_valid_with_keywords_edited.xml');
        $event->updateFromCdbXml(
            new String('someId'),
            new EventXmlString($cdbXmlEdited),
            new String('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL')
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
     * @test
     */
    public function it_can_have_labels_merged()
    {
        $cdbXml = file_get_contents(__DIR__ . '/samples/event_entryapi_valid_with_keywords.xml');
        $event = Event::createFromCdbXml(
            new String('someId'),
            new EventXmlString($cdbXml),
            new String('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL')
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
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Argument $labels should contain at least one label'
        );

        $cdbXml = file_get_contents(
            __DIR__ . '/samples/event_entryapi_valid_with_keywords.xml'
        );
        $event = Event::createFromCdbXml(
            new String('someId'),
            new EventXmlString($cdbXml),
            new String(
                'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL'
            )
        );

        $event->mergeLabels(new LabelCollection());
    }

    /**
     * @test
     */
    public function it_can_have_a_new_translation_applied()
    {
        $cdbXml = file_get_contents(__DIR__ . '/samples/event_entryapi_valid_with_keywords.xml');
        $event = Event::createFromCdbXml(
            new String('someId'),
            new EventXmlString($cdbXml),
            new String('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL')
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
        $cdbXml = file_get_contents(
            __DIR__ . '/samples/event_entryapi_valid_with_keywords.xml'
        );
        $event = Event::createFromCdbXml(
            new String('someId'),
            new EventXmlString($cdbXml),
            new String(
                'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL'
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
        $cdbXml = file_get_contents(__DIR__ . '/samples/event_entryapi_valid_with_keywords.xml');
        $event = Event::createFromCdbXml(
            new String('someId'),
            new EventXmlString($cdbXml),
            new String('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL')
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
}
