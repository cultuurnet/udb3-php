<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place;

use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Title;

interface PlaceEditingServiceInterface
{

    /**
     * Create a new place.
     *
     * @param Title $title
     * @param EventType $eventType
     * @param Address $address
     * @param CalendarBase $calendar
     * @param Theme/null $theme
     *
     * @return string $eventId
     */
    public function createPlace(Title $title, EventType $eventType, Address $address, CalendarInterface $calendar, $theme = null);

    /**
     * Update the description of a place.
     *
     * @param string $id
     * @param string $description
     */
    public function updateDescription($id, $description);

    /**
     * Update the typical age range of a place.
     *
     * @param string $id
     * @param string $ageRange
     */
    public function updateTypicalAgeRange($id, $ageRange);

    /**
     * Update the organizer of a place.
     *
     * @param string $id
     * @param string $organizerId
     */
    public function updateOrganizer($id, $organizerId);

    /**
     * Update the organizer of a place.
     *
     * @param string $id
     * @param string $organizerId
     */
    public function deleteOrganizer($id, $organizerId);

    /**
     * Update the contact info of a place.
     *
     * @param string $id
     * @param ContactPoint $contactPoint
     */
    public function updateContactPoint($id, ContactPoint $contactPoint);

    /**
     * Update the facilities for a place.
     * 
     * @param string $id
     * @param array $facilities
     */
    public function updateFacilities($id, array $facilities);

}
