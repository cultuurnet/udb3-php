<?php

namespace CultuurNet\UDB3\Offer;

class OfferIdentifierCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_only_accepts_offer_identifier_interface_instances()
    {
        $collection = new OfferIdentifierCollection();

        $collection = $collection->with(
            new IriOfferIdentifier(
                'event/1',
                '1',
                OfferType::EVENT()
            )
        );

        $this->assertEquals(1, $collection->length());

        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Expected instance of CultuurNet\UDB3\Offer\OfferIdentifierInterface, found stdClass instead.'
        );

        $collection->with(new \stdClass());
    }
}
