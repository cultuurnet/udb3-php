<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Keyword;
use CultuurNet\UDB3\Title;

class EventTest extends \PHPUnit_Framework_TestCase
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
            'LOCATION-ABC-123',
            new \DateTime(),
            new EventType('0.50.4.0.0', 'concert')
        );
    }

    /**
     * @test
     */
    public function it_can_be_tagged_with_multiple_keywords()
    {
        $this->event->tag(new Keyword('foo'));

        $this->assertEquals(
            array(new Keyword('foo')),
            $this->event->getKeywords()
        );

        $this->event->tag(new Keyword('bar'));

        $this->assertEquals(
            array(new Keyword('foo'), new Keyword('bar')),
            $this->event->getKeywords()
        );
    }

    /**
     * @test
     */
    public function it_only_applies_the_same_tag_once()
    {
        $this->event->tag(new Keyword('foo'));
        $this->event->tag(new Keyword('foo'));

        $this->assertEquals(
            array(new Keyword('foo')),
            $this->event->getKeywords()
        );
    }

    /**
     * @test
     */
    public function it_can_be_imported_from_udb2_cdbxml()
    {
        $cdbXml = file_get_contents(__DIR__ . '/EventTest.cdbxml.xml');
        $event = Event::importFromUDB2(
            'someId',
            $cdbXml,
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
        );

        $this->assertEquals(
            array(
                new Keyword('kunst'),
                new Keyword('tentoonstelling'),
                new Keyword('brugge'),
                new Keyword('grafiek'),
                new Keyword('oud sint jan'),
                new Keyword('TRAEGHE GENUINE ARTS'),
                new Keyword('janine de conink'),
                new Keyword('brugge oktober')
            ),
            $event->getKeywords()
        );
    }
}
