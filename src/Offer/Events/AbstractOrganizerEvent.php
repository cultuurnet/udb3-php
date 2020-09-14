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
    final public function __construct(string $id, $organizerId)
    {
        parent::__construct($id);
        $this->organizerId = $organizerId;
    }

    /**
     * @return string
     */
    public function getOrganizerId(): string
    {
        return $this->organizerId;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return parent::serialize() + array(
            'organizerId' => $this->organizerId,
        );
    }

    /**
     * @param array $data
     * @return static The object instance
     */
    public static function deserialize(array $data): AbstractOrganizerEvent
    {
        return new static(
            $data['item_id'],
            $data['organizerId']
        );
    }
}
