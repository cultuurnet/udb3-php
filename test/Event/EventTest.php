<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\EventXmlString;
use CultuurNet\UDB3\KeywordsString;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Title;
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
            array(new Label('foo')),
            $this->event->getLabels()
        );

        $this->event->label(new Label('bar'));

        $this->assertEquals(
            array(new Label('foo'), new Label('bar')),
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
            array(new Label('foo')),
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

        $this->assertEquals(
            [
                new Label('Foo'),
                new Label('België')
            ],
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
            [
                new Label('foo')
            ],
            $this->event->getLabels()
        );

        $this->event->unlabel(new Label('Foo'));

        $this->assertEquals(
            [],
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

        $this->assertEquals(
            array(
                new Label('kunst'),
                new Label('tentoonstelling'),
                new Label('brugge'),
                new Label('grafiek'),
                new Label('oud sint jan'),
                new Label('TRAEGHE GENUINE ARTS'),
                new Label('janine de conink'),
                new Label('brugge oktober')
            ),
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

        $this->assertEquals(
            array(
                new Label('polen'),
                new Label('slagwerk')
            ),
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

        $this->assertEquals(
            array(
                new Label('polen'),
                new Label('slagwerk'),
                new Label('test'),
                new Label('aangepast')
            ),
            $event->getLabels()
        );
    }

    /**
     * @test
     */
    public function it_can_have_labels_applied()
    {
        $cdbXml = file_get_contents(__DIR__ . '/samples/event_entryapi_valid_with_keywords.xml');
        $event = Event::createFromCdbXml(
            new String('someId'),
            new EventXmlString($cdbXml),
            new String('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL')
        );

        $keywordsString = new KeywordsString(
            file_get_contents(__DIR__ . '/samples/keywords_entryapi_two_keywords.txt')
        );

        $event->applyLabels(
            new String('someid'),
            $keywordsString
        );

        $this->assertEquals(
            array(
                new Label('muziek', true),
                new Label('orkest', false)
            ),
            $event->getLabels()
        );
    }
}
