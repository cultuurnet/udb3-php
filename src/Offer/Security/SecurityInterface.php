<?php

namespace CultuurNet\UDB3\Offer\Security;

use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;
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
     * @param AuthorizableCommandInterface $command
     * @return bool
     * @internal param String $offerId
     */
    public function isAuthorized(AuthorizableCommandInterface $command);
}
