<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\Place;

use CultuurNet\UDB3\Place\Place;

/**
 * Imports places from UDB2 (where they are called 'actors') into UDB3.
 */
interface PlaceImporterInterface
{
    /**
     * @param string $placeId
     * @return Place
     */
    public function updatePlaceFromUDB2($placeId);

    /**
     * @param string $placeId
     * @return Place
     */
    public function createPlaceFromUDB2($placeId);
}
