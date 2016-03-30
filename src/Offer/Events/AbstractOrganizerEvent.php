<?php

namespace CultuurNet\UDB3\Offer\Events;

abstract class AbstractOrganizerEvent extends AbstractEvent
{
    /**
     * The organizer id to delete.
     * @var string
     */
    protected $organizerId;

    /**
     * @param string $id
     * @param string $organizerId
     */
    public function __construct($id, $organizerId)
    {
        parent::__construct($id);
        $this->organizerId = $organizerId;
    }

    /**
     * @return string
     */
    public function getOrganizerId()
    {
        return $this->organizerId;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'organizerId' => $this->organizerId,
        );
    }
}
