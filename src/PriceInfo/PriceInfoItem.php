<?php

namespace CultuurNet\UDB3\PriceInfo;

use Broadway\Serializer\SerializableInterface;
use ValueObjects\Money\Currency;
use ValueObjects\String\String as StringLiteral;

class PriceInfoItem implements SerializableInterface
{
    /**
     * @var string
     */
    private $priceCategoryString;

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
     * @param PriceCategory $category
     * @param StringLiteral $name
     * @param Price $price
     * @param Currency $currency
     */
    public function __construct(
        PriceCategory $category,
        StringLiteral $name,
        Price $price,
        Currency $currency
    ) {
        $this->priceCategoryString = $category->toNative();
        $this->name = $name;
        $this->price = $price;
        $this->currencyCodeString = $currency->getCode()->toNative();
    }

    /**
     * @return PriceCategory
     */
    public function getCategory()
    {
        return PriceCategory::fromNative($this->priceCategoryString);
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
            'category' => $this->priceCategoryString,
            'name' => $this->name->toNative(),
            'price' => $this->price->toNative(),
            'currency' => $this->currencyCodeString,
        ];
    }

    /**
     * @param array $data
     * @return PriceInfoItem
     */
    public static function deserialize(array $data)
    {
        return new PriceInfoItem(
            PriceCategory::fromNative($data['category']),
            new StringLiteral($data['name']),
            new Price($data['price']),
            Currency::fromNative($data['currency'])
        );
    }
}
