<?php

namespace CultuurNet\UDB3\Offer\Security;

use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;
use CultuurNet\UDB3\Offer\Commands\PreflightCommand;
use CultuurNet\UDB3\Offer\ReadModel\Permission\PermissionQueryInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use CultuurNet\UDB3\Security\UserIdentificationInterface;
use ValueObjects\String\String as StringLiteral;

class Security implements SecurityInterface
{
    /**
     * @var UserIdentificationInterface
     */
    private $userIdentification;

    /**
     * @var PermissionQueryInterface
     */
    private $permissionRepository;

    /**
     * @var UserPermissionMatcherInterface
     */
    private $userPermissionMatcher;

    /**
     * Security constructor.
     * @param UserIdentificationInterface $userIdentification
     * @param PermissionQueryInterface $permissionRepository
     * @param UserPermissionMatcherInterface $userPermissionMatcher
     */
    public function __construct(
        UserIdentificationInterface $userIdentification,
        PermissionQueryInterface $permissionRepository,
        UserPermissionMatcherInterface $userPermissionMatcher
    ) {
        $this->userIdentification = $userIdentification;
        $this->permissionRepository = $permissionRepository;
        $this->userPermissionMatcher = $userPermissionMatcher;
    }

    /**
     * @inheritdoc
     */
    public function allowsUpdateWithCdbXml(StringLiteral $offerId)
    {
        return $this->currentUiTIDUserCanEditOffer(
            $offerId,
            new PreflightCommand($offerId, Permission::AANBOD_BEWERKEN())
        );
    }

    /**
     * @inheritdoc
     */
    public function isAuthorized(AuthorizableCommandInterface $command)
    {
        $offerId = new StringLiteral($command->getItemId());

        return $this->currentUiTIDUserCanEditOffer($offerId, $command);
    }

    /**
     * @param StringLiteral $offerId
     * @param AuthorizableCommandInterface $command
     * @return bool
     */
    private function currentUiTIDUserCanEditOffer(
        StringLiteral $offerId,
        AuthorizableCommandInterface $command
    ) {
        if (!$this->userIdentification->getId()) {
            return false;
        }

        // Check if superuser. If so return true.
        if ($this->userIdentification->isGodUser()) {
            return true;
        }

        // Then check if user is owner of the offer. IF so, return true.
        $editableEvents = $this->permissionRepository->getEditableOffers(
            $this->userIdentification->getId()
        );
        if (in_array($offerId, $editableEvents)) {
            return true;
        }

        // Check role permissions and constraint. IF ok true. Else false.
        if ($this->userPermissionMatcher->itMatchesOffer(
            $this->userIdentification->getId(),
            $command->getPermission(),
            $offerId
        )) {
            return true;
        }

        return false;
    }
}
