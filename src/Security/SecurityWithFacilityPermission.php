<?php

namespace CultuurNet\UDB3\Security;

use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;
use CultuurNet\UDB3\Offer\Security\Permission\PermissionVoterInterface;
use CultuurNet\UDB3\Place\Commands\UpdateFacilities;
use CultuurNet\UDB3\Security\SecurityDecoratorBase;
use CultuurNet\UDB3\Security\SecurityInterface;
use CultuurNet\UDB3\Security\UserIdentificationInterface;
use ValueObjects\StringLiteral\StringLiteral;

class SecurityWithFacilityPermission extends SecurityDecoratorBase
{
    /**
     * @var UserIdentificationInterface
     */
    private $userIdentification;

    /**
     * @var PermissionVoterInterface
     */
    private $permissionVoter;

    /**
     * @param SecurityInterface $decoratee
     * @param UserIdentificationInterface $userIdentification
     * @param PermissionVoterInterface $permissionVoter
     */
    public function __construct(
        SecurityInterface $decoratee,
        UserIdentificationInterface $userIdentification,
        PermissionVoterInterface $permissionVoter
    ) {
        parent::__construct($decoratee);

        $this->userIdentification = $userIdentification;
        $this->permissionVoter = $permissionVoter;
    }

    /**
     * @inheritdoc
     */
    public function isAuthorized(AuthorizableCommandInterface $command)
    {
        //@todo: When extending for events create an interface.
        // https://jira.uitdatabank.be/browse/III-2413
        if ($command instanceof UpdateFacilities) {
            return $this->permissionVoter->isAllowed(
                $command->getPermission(),
                new StringLiteral(''),
                $this->userIdentification->getId()
            );
        }

        return parent::isAuthorized($command);
    }
}
