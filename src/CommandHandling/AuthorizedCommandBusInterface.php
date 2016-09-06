<?php

namespace CultuurNet\UDB3\CommandHandling;

use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;
use CultuurNet\UDB3\Security\UserIdentificationInterface;

interface AuthorizedCommandBusInterface
{
    /**
     * @param AuthorizableCommandInterface $command
     * @return bool
     */
    public function isAuthorized(AuthorizableCommandInterface $command);

    /**
     * @return UserIdentificationInterface
     */
    public function getUserIdentification();
}
