<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place\ReadModel\Lookup;

interface PlaceLookupServiceInterface
{
    /**
     * @param string $postalCode
     *
     * @return string[]
     *   A list of place IDs.
     */
    public function findPlacesByPostalCode($postalCode);
}
