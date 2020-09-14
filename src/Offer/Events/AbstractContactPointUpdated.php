<?php

namespace CultuurNet\UDB3\Offer\Events;

use CultuurNet\UDB3\ContactPoint;

abstract class AbstractContactPointUpdated extends AbstractEvent
{
    /**
     * ContactPoint to be saved
     * @var ContactPoint
     */
    protected $contactPoint;

    final public function __construct(string $id, ContactPoint $contactPoint)
    {
        parent::__construct($id);
        $this->contactPoint = $contactPoint;
    }

    public function getContactPoint(): ContactPoint
    {
        return $this->contactPoint;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'contactPoint' => $this->contactPoint->serialize(),
        );
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['item_id'], ContactPoint::deserialize($data['contactPoint']));
    }
}
