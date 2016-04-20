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

    public function getContactPoint()
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
     * @param string $id
     * @param ContactPoint $contactPoint
     */
    public function __construct($id, ContactPoint $contactPoint)
    {
        parent::__construct($id);
        $this->contactPoint = $contactPoint;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['item_id'], ContactPoint::deserialize($data['contactPoint']));
    }
}
