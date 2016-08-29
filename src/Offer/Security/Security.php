<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Offer\Security;

use CultuurNet\SymfonySecurityJwt\Authentication\JwtUserToken;
use CultuurNet\SymfonySecurityOAuth\Security\OAuthToken;
use CultuurNet\SymfonySecurityOAuthUitid\User;
use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;
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
    public function isAuthorized(AuthorizableCommandInterface $command)
    {
        $offerId = new String($command->getItemId());

        return $this->currentUiTIDUserCanEditOffer($offerId);
    }

    /**
     * @param String $offerId
     * @return bool
     */
    private function currentUiTIDUserCanEditOffer(String $offerId)
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return false;
        }

        if ($token instanceof OAuthToken) {
            /* @var User $user */
            $user = $token->getUser();
            $userId = new String($user->getUid());
        } elseif ($token instanceof JwtUserToken) {
            $userId = new String($token->getCredentials()->getClaim('uid'));
        }

        if (!isset($userId)) {
            return false;
        }

        // Check if superuser. If so return true.

        // Then check if user is owner of the offer. IF so, return true.
        $editableEvents = $this->permissionRepository->getEditableOffers(
            $userId
        );

        // Check role permissions and constraint. IF ok true. Else false.

        return in_array($offerId, $editableEvents);
    }
}
