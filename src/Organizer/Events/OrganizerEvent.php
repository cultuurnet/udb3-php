<?php

namespace CultuurNet\UDB3\Organizer\Events;

use Broadway\Serializer\SerializableInterface;

abstract class OrganizerEvent implements SerializableInterface
{
    /**
     * @var string
     */
    protected $organizerId;

    public function __construct(string $organizerId)
    {
        $this->organizerId = $organizerId;
    }

    public function getOrganizerId(): string
    {
        return $this->organizerId;
    }

    public function serialize(): array
    {
        return array(
          'organizer_id' => $this->organizerId,
        );
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['organizer_id']);
    }
}
