<?php

namespace CultuurNet\UDB3\Offer\Item\Events;

use Broadway\Serializer\SerializableInterface;

class ItemCreated implements SerializableInterface
{
    /**
     * @var string
     */
    protected $itemId;

    /**
     * @param string $itemId
     */
    public function __construct($itemId)
    {
        $this->itemId = $itemId;
    }

    /**
     * @return string
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @param array $data
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static($data['itemId']);
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array('itemId' => $this->itemId);
    }
}
