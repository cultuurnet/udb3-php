<?php

namespace CultuurNet\UDB3\PriceInfo;

use ValueObjects\Money\Currency;
use ValueObjects\StringLiteral\StringLiteral;

class PriceInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BasePrice
     */
    private $basePrice;

    /**
     * @var Tariff[]
     */
    private $tariffs;

    /**
     * @var PriceInfo
     */
    private $priceInfo;

    public function setUp()
    {
        $this->basePrice = new BasePrice(
            Price::fromFloat(10.5),
            Currency::fromNative('EUR')
        );

        $this->tariffs = [
            new Tariff(
                new StringLiteral('Werkloze dodo kwekers'),
                new Price(0),
                Currency::fromNative('EUR')
            )
        ];

        $this->priceInfo = (new PriceInfo($this->basePrice))
            ->withExtraTariff($this->tariffs[0]);
    }

    /**
     * @test
     */
    public function it_returns_the_base_price()
    {
        $this->assertEquals($this->basePrice, $this->priceInfo->getBasePrice());
    }

    /**
     * @test
     */
    public function it_returns_any_extra_tariffs()
    {
        $this->assertEquals($this->tariffs, $this->priceInfo->getTariffs());
    }

    /**
     * @test
     */
    public function it_can_be_serialized_and_deserialized()
    {
        $serialized = $this->priceInfo->serialize();
        $deserialized = PriceInfo::deserialize($serialized);

        $this->assertEquals($this->priceInfo, $deserialized);
    }
}
