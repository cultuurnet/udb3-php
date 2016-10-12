<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\EventNotFoundException;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Media\MediaObject;
use CultuurNet\UDB3\Title;
use ValueObjects\String\String;

interface EventEditingServiceInterface
{
    /**
     * Update the description of an event.
     *
     * @param string $id
     * @param string $description
     */
    public function updateDescription($id, $description);

    /**
     * Update the typical age range of an event.
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
     * Update the organizer of an event.
     *
     * @param string $id
     * @param string $organizerId
     */
    public function updateOrganizer($id, $organizerId);

    /**
     * Update the organizer of an event.
     *
     * @param string $id
     * @param string $organizerId
     */
    public function deleteOrganizer($id, $organizerId);

    /**
     * Update the contact point of an event.
     *
     * @param string $id
     * @param ContactPoint $contactPoint
     */
    public function updateContactPoint($id, ContactPoint $contactPoint);

    /**
     * Add an image to the event.
     *
     * @param string $id
     * @param Image $image
     */
    public function addImage($id, Image $image);

    /**
     * Update an image of the event.
     *
     * @param $id
     * @param Image $image
     * @param \ValueObjects\String\String $description
     * @param \ValueObjects\String\String $copyrightHolder
     *
     * @return string
     *  The command id for this task.
     */
    public function updateImage(
        $id,
        Image $image,
        String $description,
        String $copyrightHolder
    );

    /**
     * Remove an image from an event.
     *
     * @param string $id
     * @param Image $image
     */
    public function removeImage($id, Image $image);

    /**
     * @param Title $title
     * @param EventType $eventType
     * @param \CultuurNet\UDB3\Location\Location $location
     * @param CalendarBase $calendar
     * @param Theme/null $theme
     *
     * @return string $eventId
     */
    public function createEvent(Title $title, EventType $eventType, Location $location, CalendarInterface $calendar, $theme = null);

    /**
     * @param string $eventId
     * @param Title $title
     * @param EventType $eventType
     * @param \CultuurNet\UDB3\Location\Location $location
     * @param CalendarBase $calendar
     * @param Theme/null $theme
     *
     * @return string $eventId
     */
    public function updateMajorInfo($eventId, Title $title, EventType $eventType, Location $location, CalendarInterface $calendar, $theme = null);

    /**
     * @param string $eventId
     *
     * @return string $eventId
     */
    public function deleteEvent($eventId);
}
