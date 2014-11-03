<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;


class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Event
     */
    protected $event;

    public function setUp()
    {
        $this->event = Event::create('foo');
    }

    /**
     * @test
     */
    public function it_can_be_tagged_with_multiple_keywords()
    {
        $this->event->tag('foo');

        $this->assertEquals(
            array('foo'),
            $this->event->getKeywords()
        );

        $this->event->tag('bar');

        $this->assertEquals(
            array('foo', 'bar'),
            $this->event->getKeywords()
        );
    }

    /**
     * @test
     */
    public function it_only_applies_the_same_tag_once()
    {
        $this->event->tag('foo');
        $this->event->tag('foo');

        $this->assertEquals(
            array('foo'),
            $this->event->getKeywords()
        );
    }
} 
