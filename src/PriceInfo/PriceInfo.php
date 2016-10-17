<?php

namespace CultuurNet\UDB3\PriceInfo;

use Broadway\Serializer\SerializableInterface;

class PriceInfo implements SerializableInterface
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
     * @param BasePrice $basePrice
     */
    public function __construct(BasePrice $basePrice)
    {
        $this->basePrice = $basePrice;
        $this->tariffs = [];
    }

    /**
     * @param Tariff $tariff
     * @return PriceInfo
     */
    public function withExtraTariff(Tariff $tariff)
    {
        $c = clone $this;
        $c->tariffs[] = $tariff;
        return $c;
    }

    /**
     * @return BasePrice
     */
    public function getBasePrice()
    {
        return $this->basePrice;
    }

    /**
     * @return Tariff[]
     */
    public function getTariffs()
    {
        return $this->tariffs;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        $serialized = [
            'base' => $this->basePrice->serialize(),
            'tariffs' => [],
        ];

        foreach ($this->tariffs as $tariff) {
            $serialized['tariffs'][] = $tariff->serialize();
        }

        return $serialized;
    }

    /**
     * @param array $data
     * @return PriceInfo
     */
    public static function deserialize(array $data)
    {
        $basePriceInfo = BasePrice::deserialize($data['base']);

        $priceInfo = new PriceInfo($basePriceInfo);

        foreach ($data['tariffs'] as $tariffData) {
            $priceInfo = $priceInfo->withExtraTariff(
                Tariff::deserialize($tariffData)
            );
        }

        return $priceInfo;
    }
}
