<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\Keyword;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Title;

interface EventEditingServiceInterface
{
    /**
     * @param string $eventId
     * @param Language $language
     * @param string $title
     * @return string command id
     * @throws EventNotFoundException
     */
    public function translateTitle($eventId, Language $language, $title);

    /**
     * Update the description for a language.
     *
     * @param string $eventId
     * @param Language $language
     * @param string $description
     * @return string command id
     * @throws EventNotFoundException
     */
    public function translateDescription($eventId, Language $language, $description);

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
     * @param string $eventId
     * @param Keyword $keyword
     * @return string command id
     * @throws EventNotFoundException
     */
    public function tag($eventId, Keyword $keyword);

    /**
     * @param string $eventId
     * @param Keyword $keyword
     * @return string command id
     * @throws EventNotFoundException
     */
    public function eraseTag($eventId, Keyword $keyword);

    /**
     * @param Title $title
     * @param EventType $eventType
     * @param Location $location
     * @param CalendarBase $calendar
     * @param Theme/null $theme
     *
     * @return string $eventId
     */
    public function createEvent(Title $title, EventType $eventType, Location $location, CalendarInterface $calendar, $theme = null);
}
