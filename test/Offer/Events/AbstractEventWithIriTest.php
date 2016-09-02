<?php

namespace CultuurNet\UDB3\Offer\Events;

class AbstractEventWithIriTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractEventWithIri
     */
    protected $event;

    /**
     * @var string
     */
    protected $iri;

    public function setUp()
    {
        $this->iri = 'event/1';
        $this->event = new MockAbstractEventWithIri($this->iri);
    }

    /**
     * @test
     */
    public function it_returns_the_iri()
    {
        $this->assertEquals('event/1', $this->event->getIri());
    }

    /**
     * @test
     */
    public function it_can_be_serialized()
    {
        $serialized = $this->event->serialize();
        $deserialized = MockAbstractEventWithIri::deserialize($serialized);

        $this->assertEquals($this->event, $deserialized);
    }
}
