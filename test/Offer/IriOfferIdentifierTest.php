<?php

namespace CultuurNet\UDB3\Offer;

class IriOfferIdentifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_extracts_the_id_from_the_iri()
    {
        $expectedId = 1;
        $iri = "event/{$expectedId}";
        $type = OfferType::EVENT();

        $identifier = new IriOfferIdentifier($iri, $type);

        $this->assertEquals($expectedId, $identifier->getId());
        $this->assertEquals($iri, $identifier->getIri());
        $this->assertEquals($type, $identifier->getType());
    }

    /**
     * @test
     */
    public function it_ignores_trailing_slashes_in_the_iri()
    {
        $expectedId = 1;
        $iri = "event/{$expectedId}/";
        $type = OfferType::EVENT();

        $identifier = new IriOfferIdentifier($iri, $type);

        $this->assertEquals($expectedId, $identifier->getId());
    }

    /**
     * @test
     */
    public function it_can_be_serialized_and_unserialized()
    {
        $identifier = new IriOfferIdentifier(
            'place/1',
            OfferType::PLACE()
        );

        $serialized = serialize($identifier);
        $unserialized = unserialize($serialized);

        $this->assertEquals($identifier, $unserialized);
    }
}
