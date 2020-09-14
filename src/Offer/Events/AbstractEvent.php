<?php

namespace CultuurNet\UDB3\Offer\Events;

use Broadway\Serializer\SerializableInterface;

abstract class AbstractEvent implements SerializableInterface
{
    /**
     * @var string
     */
    protected $itemId;

    /**
     * @param $itemId
     *  The id of the item that is the subject of the event.
     */
    public function __construct($itemId)
    {
        if (!is_string($itemId)) {
            throw new \InvalidArgumentException(
                'Expected itemId to be a string, received ' . gettype($itemId)
            );
        }

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
     * @return array
     */
    public function serialize()
    {
        return array(
            'item_id' => $this->itemId,
        );
    }
}
