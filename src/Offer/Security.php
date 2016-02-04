<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Offer;

use CultuurNet\SymfonySecurityOAuthUitid\User;
use CultuurNet\UDB3\Offer\ReadModel\Permission\PermissionQueryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use ValueObjects\String\String;

class Security implements SecurityInterface
{
    /**
     * @var PermissionQueryInterface
     */
    private $permissionRepository;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        PermissionQueryInterface $permissionRepository
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * @inheritdoc
     */
    public function allowsUpdateWithCdbXml(String $offerId)
    {
        return $this->currentUiTIDUserCanEditOffer($offerId);
    }

    /**
     * @inheritdoc
     */
    public function allowsUpdates(String $offerId)
    {
        return $this->currentUiTIDUserCanEditOffer($offerId);
    }

    /**
     * @param String $eventId
     * @return bool
     */
    private function currentUiTIDUserCanEditOffer(String $offerId)
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return false;
        }

        $user = $token->getUser();

        if ($user instanceof User) {
            $userId = new String($user->getUid());
        } else if ($user instanceof \CultuurNet\UiTIDProvider\User\User) {
            $userId = new String($user->id);
        }

        if (!isset($userId)) {
            return false;
        }

        $editableEvents = $this->permissionRepository->getEditableOffers(
            $userId
        );

        return in_array($offerId, $editableEvents);
    }
}
