<?php

namespace CultuurNet\UDB3\Offer;

class OfferTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider offerTypeDataProvider
     *
     * @param string $enumValue
     * @param OfferType $expectedOfferType
     */
    public function it_can_be_created_from_a_value_with_incorrect_casing(
        $enumValue,
        OfferType $expectedOfferType
    ) {
        $actualOfferType = OfferType::fromCaseInsensitiveValue($enumValue);
        $this->assertTrue($expectedOfferType->sameValueAs($actualOfferType));
    }

    public function offerTypeDataProvider()
    {
        return [
            [
                'place',
                OfferType::PLACE(),
            ],
            [
                'eVeNt',
                OfferType::EVENT(),
            ],
            [
                'Place',
                OfferType::PLACE(),
            ],
            [
                'EVENT',
                OfferType::EVENT(),
            ],
        ];
    }
}
