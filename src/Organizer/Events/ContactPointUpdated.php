<?php

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\ContactPoint;

class ContactPointUpdated extends OrganizerEvent
{
    /**
     * @var ContactPoint
     */
    private $contactPoint;

    /**
     * @param string $organizerId
     * @param ContactPoint $contactPoint
     */
    public function __construct(
        $organizerId,
        ContactPoint $contactPoint
    ) {
        parent::__construct($organizerId);
        $this->contactPoint = $contactPoint;
    }

    /**
     * @return ContactPoint
     */
    public function getContactPoint()
    {
        return $this->contactPoint;
    }
}
