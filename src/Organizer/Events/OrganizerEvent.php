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

    protected $organizerId;

    public function __construct($organizerId)
    {
        $this->organizerId = $organizerId;
    }

    public function getOrganizerId()
    {
        return $this->organizerId;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
          'organizer_id' => $this->organizerId,
        );
    }
}
