<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Offer;

use ValueObjects\String\String;

interface SecurityInterface
{
    /**
     * @param String $offerId
     * @return boolean
     */
    public function allowsUpdateWithCdbXml(String $offerId);

    /**
     * Returns if the event allows updates through the UDB3 core APIs.
     *
     * @param String $offerId
     * @return boolean
     */
    public function allowsUpdates(String $offerId);
}
