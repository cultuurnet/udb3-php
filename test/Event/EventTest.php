<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Title;
use PHPUnit_Framework_TestCase;

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
}
