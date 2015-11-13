<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\SymfonySecurityOAuthUitid\User;
use CultuurNet\UDB3\Event\ReadModel\Permission\PermissionQueryInterface;
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
     * @param String $eventId
     * @return bool
     */
    private function currentUiTIDUserCanEditEvent(String $eventId)
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $editableEvents = $this->permissionRepository->getEditableEvents(
            new String($user->getUid())
        );

        return in_array($eventId, $editableEvents);
    }
}
