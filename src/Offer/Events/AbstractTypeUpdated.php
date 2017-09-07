<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Event\EventType;

abstract class AbstractTypeUpdated extends AbstractEvent
{
    /**
     * @var EventType
     */
    protected $type;

    /**
     * @param string $itemId
     * @param EventType $type
     */
    public function __construct($itemId, EventType $type)
    {
        parent::__construct($itemId);
        $this->type = $type;
    }

    /**
     * @return EventType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return parent::serialize() + [
            'type' => $this->type->serialize(),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        return new static($data['item_id'], EventType::deserialize($data['type']));
    }
}
