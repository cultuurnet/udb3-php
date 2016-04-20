<?php

namespace CultuurNet\UDB3\Offer\Events;

abstract class AbstractDescriptionUpdated extends AbstractEvent
{
    /**
     * The new description.
     * @var string
     */
    protected $description;

    /**
     * @param string $id
     * @param string $description
     */
    public function __construct($id, $description)
    {
        parent::__construct($id);
        $this->description = $description;
    }

    /**
     * @return string
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
            'description' => $this->description,
        );
    }

    /**
     * @return AbstractDescriptionUpdated
     */
    public static function deserialize(array $data)
    {
        return new static($data['item_id'], $data['description']);
    }
}
