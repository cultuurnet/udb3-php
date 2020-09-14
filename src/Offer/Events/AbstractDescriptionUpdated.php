<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\Description;

abstract class AbstractDescriptionUpdated extends AbstractEvent
{
    /**
     * The new description.
     * @var Description
     */
    protected $description;

    /**
     * @param string $id
     * @param Description $description
     */
    final public function __construct(string $id, Description $description)
    {
        parent::__construct($id);
        $this->description = $description;
    }

    /**
     * @return Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'description' => $this->description->toNative(),
        );
    }

    /**
     * @param array $data
     * @return AbstractDescriptionUpdated
     */
    public static function deserialize(array $data)
    {
        return new static($data['item_id'], new Description($data['description']));
    }
}
