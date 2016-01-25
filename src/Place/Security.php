<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place;

use CultuurNet\SymfonySecurityOAuthUitid\User;
use CultuurNet\UDB3\Place\ReadModel\Permission\PermissionQueryInterface;
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
        return $this->currentUiTIDUserCanEditPlace($eventId);
    }

    /**
     * @inheritdoc
     */
    public function allowsUpdates(String $eventId)
    {
        return $this->currentUiTIDUserCanEditPlace($eventId);
    }

    /**
     * @param String $placeId
     * @return bool
     */
    private function currentUiTIDUserCanEditPlace(String $placeId)
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

        $editableEvents = $this->permissionRepository->getEditablePlaces(
            $userId
        );

        return in_array($placeId, $editableEvents);
    }
}
