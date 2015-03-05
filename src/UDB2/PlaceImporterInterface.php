<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use CultuurNet\UDB3\Place\Place;

/**
 * Imports actors from UDB2 into UDB3.
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
