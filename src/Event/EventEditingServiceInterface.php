<?php

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\ValueObjects\Audience;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Title;
use ValueObjects\String\String as StringLiteral;

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
        StringLiteral $description,
        StringLiteral $copyrightHolder
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
     * @param Location $location
     * @param CalendarInterface $calendar
     * @param Theme/null $theme
     *
     * @return string $eventId
     */
    public function createEvent(Title $title, EventType $eventType, Location $location, CalendarInterface $calendar, $theme = null);

    /**
     * @param string $originalEventId
     * @param CalendarInterface $calendar
     * @return string $eventId
     *
     * @throws \InvalidArgumentException
     */
    public function copyEvent($originalEventId, CalendarInterface $calendar);

    /**
     * @param string $eventId
     * @param Title $title
     * @param EventType $eventType
     * @param Location $location
     * @param CalendarInterface $calendar
     * @param Theme/null $theme
     *
     * @return string $commandId
     */
    public function updateMajorInfo($eventId, Title $title, EventType $eventType, Location $location, CalendarInterface $calendar, $theme = null);

    /**
     * @param string $eventId
     * @param Audience $audience
     * @return string $commandId
     */
    public function updateAudience($eventId, Audience $audience);

    /**
     * @param string $eventId
     *
     * @return string $commandId
     */
    public function deleteEvent($eventId);
}
