<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;


use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\Keyword;
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
} 
