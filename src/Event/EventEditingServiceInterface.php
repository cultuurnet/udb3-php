<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\Keyword;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Theme;

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
     * Update the main language description.
     * @param string $eventId
     * @param string $description
     */
    public function updateDescription($eventId, $description);

    /**
     * Update the age range.
     *
     * @param string $eventId
     * @param string $ageRange
     * @return string command id
     * @throws EventNotFoundException
     */
    public function updateTypicalAgeRange($eventId, $ageRange);

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
     * @param Theme $theme
     * @param Location $location
     * @param CalendarBase $calendar
     *
     * @return string $eventId
     */
    public function createEvent(Title $title, EventType $eventType, Theme $theme, Location $location, CalendarInterface $calendar);
}
