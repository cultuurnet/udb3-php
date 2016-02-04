<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\SymfonySecurityOAuthUitid\User;
use CultuurNet\UDB3\Offer\ReadModel\Permission\PermissionQueryInterface;
use CultuurNet\UDB3\Offer\SecurityInterface;
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
    public function allowsUpdateWithCdbXml(String $eventId)
    {
        return $this->currentUiTIDUserCanEditEvent($eventId);
    }

    /**
     * @inheritdoc
     */
    public function allowsUpdates(String $eventId)
    {
        return $this->currentUiTIDUserCanEditEvent($eventId);
    }

    /**
     * @param String $eventId
     * @return bool
     */
    private function currentUiTIDUserCanEditEvent(String $eventId)
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

        return in_array($eventId, $editableEvents);
    }
}
