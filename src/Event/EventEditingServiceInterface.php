<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Variations\Purpose;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;

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
     * @param string $eventId
     * @param Language $language
     * @param string $description
     * @return string command id
     * @throws EventNotFoundException
     */
    public function translateDescription($eventId, Language $language, $description);

    /**
     * @param string $eventId
     * @param string $editorId
     * @param Purpose $purpose
     * @param string $description
     * @return string command id
     * @throws EventNotFoundException
     */
    public function editDescription($eventId, $editorId, Purpose $purpose, $description);

    /**
     * @param string $eventId
     * @param Label $label
     * @return string command id
     * @throws EventNotFoundException
     */
    public function label($eventId, Label $label);

    /**
     * @param string $eventId
     * @param Label $label
     * @return string command id
     * @throws EventNotFoundException
     */
    public function unlabel($eventId, Label $label);

    /**
     * @param Title $title
     * @param string $location
     * @param mixed $date
     *
     * @return string $eventId
     */
    public function createEvent(Title $title, $location, $date);
}
