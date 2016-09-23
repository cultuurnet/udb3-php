<?php

namespace CultuurNet\UDB3\PriceInfo;

use Broadway\Serializer\SerializableInterface;

class PriceInfo implements SerializableInterface
{
    /**
     * @var PriceInfoItem[]
     */
    private $items;

    /**
     * @param PriceInfoItem[] $items
     */
    public function __construct(array $items)
    {
        $this->guardPriceInfoItems($items);
        $this->items = $items;
    }

    /**
     * @return PriceInfoItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        $serialized = [];

        foreach ($this->items as $item) {
            $serialized[] = $item->serialize();
        }

        return $serialized;
    }

    /**
     * @param array $data
     * @return PriceInfo
     */
    public static function deserialize(array $data)
    {
        $items = [];

        foreach ($data as $itemData) {
            $items[] = PriceInfoItem::deserialize($itemData);
        }

        return new PriceInfo($items);
    }

    /**
     * @param array $items
     */
    private function guardPriceInfoItems(array $items)
    {
        $baseItems = 0;

        foreach ($items as $item) {
            if (!($item instanceof PriceInfoItem)) {
                throw new \InvalidArgumentException('PriceInfo only allows PriceInfoItem children.');
            }

            if ($item->getCategory()->sameValueAs(PriceCategory::BASE())) {
                $baseItems++;
            }
        }

        if ($baseItems !== 1) {
            throw new \InvalidArgumentException(
                'PriceInfo should always contain exactly one PriceInfoItem with the "base" category.'
            );
        }
    }
}
