<?php

namespace CultuurNet\UDB3\PriceInfo;

use Broadway\Serializer\SerializableInterface;
use ValueObjects\Money\Currency;
use ValueObjects\StringLiteral\StringLiteral;

class Tariff implements SerializableInterface
{
    /**
     * @var StringLiteral
     */
    private $name;

    /**
     * @var Price
     */
    private $price;

    /**
     * @var string
     */
    private $currencyCodeString;

    /**
     * @param StringLiteral $name
     * @param Price $price
     * @param Currency $currency
     */
    public function __construct(
        StringLiteral $name,
        Price $price,
        Currency $currency
    ) {
        $this->name = $name;
        $this->price = $price;
        $this->currencyCodeString = $currency->getCode()->toNative();
    }

    /**
     * @return StringLiteral
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return Currency::fromNative($this->currencyCodeString);
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return [
            'name' => $this->name->toNative(),
            'price' => $this->price->toNative(),
            'currency' => $this->currencyCodeString,
        ];
    }

    /**
     * @param array $data
     * @return Tariff
     */
    public static function deserialize(array $data)
    {
        return new Tariff(
            new StringLiteral($data['name']),
            new Price($data['price']),
            Currency::fromNative($data['currency'])
        );
    }
}
