<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place\ReadModel\Relations;

interface RepositoryInterface
{
    public function storeRelations($placeId, $organizerId);

    public function getPlacesOrganizedByOrganizer($organizerId);
}
