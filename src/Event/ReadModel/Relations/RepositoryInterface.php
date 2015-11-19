<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\Relations;

interface RepositoryInterface
{
    /**
     * @param string $eventId
     * @param string $placeId
     * @param string $organizerId
     */
    public function storeRelations($eventId, $placeId, $organizerId);

    /**
     * @param string $eventId
     * @param string $organizerId
     */
    public function storeOrganizer($eventId, $organizerId);

    public function getEventsLocatedAtPlace($placeId);

    public function getEventsOrganizedByOrganizer($organizerId);

    public function removeRelations($eventId);
}
