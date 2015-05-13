<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

interface EventCdbXmlServiceInterface
{
    /**
     * @param string $eventId
     * @return string
     * @throws EventNotFoundException If the event can not be found.
     */
    public function getCdbXmlOfEvent($eventId);

    /**
     * @return string
     */
    public function getCdbXmlNamespaceUri();
}
