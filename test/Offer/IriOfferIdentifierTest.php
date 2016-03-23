<?php

namespace CultuurNet\UDB3\Offer;

class IriOfferIdentifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IriOfferIdentifier
     */
    private $identifier;

    public function setUp()
    {
        $this->identifier = new IriOfferIdentifier(
            'place/1',
            '1',
            OfferType::PLACE()
        );
    }

    /**
     * @test
     */
    public function it_can_be_serialized_and_unserialized()
    {
        $serialized = serialize($this->identifier);
        $unserialized = unserialize($serialized);

        $this->assertEquals($this->identifier, $unserialized);
    }

    /**
     * @test
     */
    public function it_returns_all_properties()
    {
        $this->assertEquals('place/1', $this->identifier->getIri());
        $this->assertEquals('1', $this->identifier->getId());
        $this->assertEquals(OfferType::PLACE(), $this->identifier->getType());
    }
}
