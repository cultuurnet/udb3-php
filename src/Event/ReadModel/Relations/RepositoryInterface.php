<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\Relations;

interface RepositoryInterface
{
    public function storeRelations($eventId, $placeId, $organizerId);

    public function getEventsLocatedAtPlace($placeId);

    public function getEventsOrganizedByOrganizer($organizerId);

    public function removeRelations($eventId);
}
