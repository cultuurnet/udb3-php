<?php

namespace CultuurNet\UDB3\Offer;

use ValueObjects\Web\Url;

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
                Url::fromNative('http://du.de/event/1'),
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
