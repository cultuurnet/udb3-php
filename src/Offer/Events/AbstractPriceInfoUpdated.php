<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\PriceInfo\PriceInfo;

abstract class AbstractPriceInfoUpdated extends AbstractEvent
{
    /**
     * @var PriceInfo
     */
    protected $priceInfo;

    /**
     * @param string $itemId
     * @param PriceInfo $priceInfo
     */
    final public function __construct(string $itemId, PriceInfo $priceInfo)
    {
        parent::__construct($itemId);
        $this->priceInfo = $priceInfo;
    }

    /**
     * @return PriceInfo
     */
    public function getPriceInfo()
    {
        return $this->priceInfo;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return [
            'item_id' => $this->itemId,
            'price_info' => $this->priceInfo->serialize(),
        ];
    }

    /**
     * @param array $data
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['item_id'],
            PriceInfo::deserialize($data['price_info'])
        );
    }
}
