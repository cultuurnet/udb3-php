<?php

namespace CultuurNet\UDB3\CommandHandling;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;
use CultuurNet\UDB3\Security\CommandAuthorizationException;
use CultuurNet\UDB3\Security\SecurityInterface;
use CultuurNet\UDB3\Security\UserIdentificationInterface;

class AuthorizedCommandBus extends CommandBusDecoratorBase implements AuthorizedCommandBusInterface
{
    /**
     * @var UserIdentificationInterface
     */
    private $userIdentification;

    /**
     * @var SecurityInterface
     */
    private $security;

    /**
     * AuthorizedCommandBus constructor.
     * @param CommandBusInterface $decoratee
     * @param UserIdentificationInterface $userIdentification
     * @param SecurityInterface $security
     */
    public function __construct(
        CommandBusInterface $decoratee,
        UserIdentificationInterface $userIdentification,
        SecurityInterface $security
    ) {
        parent::__construct($decoratee);

        $this->userIdentification = $userIdentification;
        $this->security = $security;
    }

    /**
     * @inheritdoc
     */
    public function dispatch($command)
    {
        if ($command instanceof AuthorizableCommandInterface) {
            $authorized = $this->isAuthorized($command);
        } else {
            $authorized = true;
        }

        if ($authorized) {
            parent::dispatch($command);
        } else {
            throw new CommandAuthorizationException(
                $this->userIdentification->getId(),
                $command
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function isAuthorized(AuthorizableCommandInterface $command)
    {
        return $this->security->isAuthorized($command);
    }

    /**
     * @return UserIdentificationInterface
     */
    public function getUserIdentification()
    {
        return $this->userIdentification;
    }
}