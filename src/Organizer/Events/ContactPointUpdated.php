<?php

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\ContactPoint;

final class ContactPointUpdated extends OrganizerEvent
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
        string $organizerId,
        ContactPoint $contactPoint
    ) {
        parent::__construct($organizerId);
        $this->contactPoint = $contactPoint;
    }

    /**
     * @return ContactPoint
     */
    public function getContactPoint(): ContactPoint
    {
        return $this->contactPoint;
    }


    /**
     * @return array
     */
    public function serialize(): array
    {
        return parent::serialize() + [
            'contactPoint' => $this->contactPoint->serialize(),
        ];
    }

    /**
     * @param array $data
     * @return static
     */
    public static function deserialize(array $data): ContactPointUpdated
    {
        return new static(
            $data['organizer_id'],
            ContactPoint::deserialize($data['contactPoint'])
        );
    }
}
