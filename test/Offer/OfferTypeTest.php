<?php

namespace CultuurNet\UDB3\Offer;

class OfferTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_created_from_a_value_with_incorrect_casing()
    {
        // Enum values are "Place" and "Event", so lower case with an
        // uppercase first letter.
        $expectedOfferType = OfferType::PLACE();
        $actualOfferType = OfferType::fromCaseInsensitiveValue('place');
        $this->assertTrue($expectedOfferType->sameValueAs($actualOfferType));

        $expectedOfferType = OfferType::EVENT();
        $actualOfferType = OfferType::fromCaseInsensitiveValue('eVeNt');
        $this->assertTrue($expectedOfferType->sameValueAs($actualOfferType));
    }
}
