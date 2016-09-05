<?php

namespace CultuurNet\UDB3\CommandHandling;

use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;

interface AuthorizedCommandBusInterface
{
    /**
     * @param AuthorizableCommandInterface $command
     * @return bool
     */
    public function isAuthorized(AuthorizableCommandInterface $command);
}
