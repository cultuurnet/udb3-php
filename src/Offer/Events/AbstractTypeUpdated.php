<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Category;

abstract class AbstractTypeUpdated extends AbstractEvent
{
    /**
     * @var Category
     */
    protected $type;

    /**
     * @param $itemId
     * @param Category $type
     */
    public function __construct($itemId, Category $type)
    {
        parent::__construct($itemId);
        $this->type = $type;
    }

    /**
     * @return Category
     */
    public function getType()
    {
        return $this->type;
    }

    public function serialize()
    {
        return parent::serialize() + [
            'type' => $this->type->serialize(),
        ];
    }

    public static function deserialize(array $data)
    {
        return new static($data['item_id'], Category::deserialize($data['type']));
    }
}
