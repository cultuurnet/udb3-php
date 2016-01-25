<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place;

use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Media\MediaObject;
use CultuurNet\UDB3\Theme;
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
     * @param Theme|null $theme
     *
     * @return string $eventId
     */
    public function createPlace(Title $title, EventType $eventType, Address $address, CalendarInterface $calendar, Theme $theme = null);

    /**
     * @param string $id
     *
     * @return string $id
     */
    public function deletePlace($id);

    /**
     * Update the major info of a place.
     *
     * @param string $id
     * @param Title $title
     * @param EventType $eventType
     * @param Address $address
     * @param CalendarBase $calendar
     * @param Theme/null $theme
     */
    public function updateMajorInfo($id, Title $title, EventType $eventType, Address $address, CalendarInterface $calendar, $theme = null);

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
     * Delete the typical age range of a place.
     *
     * @param string $id
     */
    public function deleteTypicalAgeRange($id);

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

    /**
     * Add an image to the place.
     *
     * @param string $id
     * @param Image $image
     */
    public function addImage($id, Image $image);

    /**
     * Update an image of the place.
     *
     * @param string $id
     * @param int $indexToEdit
     * @param MediaObject $mediaObject
     */
    public function updateImage($id, $indexToEdit, MediaObject $mediaObject);

    /**
     * Delete an image of the place.
     *
     * @param string $id
     * @parma int $indexToDelete
     */
    public function deleteImage($id, $indexToDelete, $internalId = '');
}
