<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Organizer\Events\OrganizerEvent.
 */

namespace CultuurNet\UDB3\Organizer\Events;

use Broadway\Serializer\SerializableInterface;

/**
 * Abstract class for events on organizers.
 */
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

    /**
     * @return array
     */
    public function serialize(): array
    {
        return array(
          'organizer_id' => $this->organizerId,
        );
    }
}
