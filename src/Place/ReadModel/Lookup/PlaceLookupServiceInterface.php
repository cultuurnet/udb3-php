<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place\ReadModel\Lookup;

interface PlaceLookupServiceInterface
{
    /**
     * @param string $postalCode
     * @param string $country
     *
     * @return string[]
     *   A list of place IDs.
     */
    public function findPlacesByPostalCode($postalCode, $country);

    /**
     * @param string $city
     * @param string $country
     * @return string[]
     *   A list of place IDs.
     */
    public function findPlacesByCity($city, $country);
}
