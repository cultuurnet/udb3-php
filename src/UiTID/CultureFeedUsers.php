<?php

namespace CultuurNet\UDB3\UiTID;

use CultuurNet\UDB3\User\CultureFeedUserIdentityResolver;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\EmailAddress;

/**
 * @deprecated
 *   Use \CultuurNet\UDB3\User\CultureFeedUserIdentityResolver instead.
 */
class CultureFeedUsers implements UsersInterface
{
    /**
     * @var CultureFeedUserIdentityResolver
     */
    private $userIdentityResolver;

    /**
     * @param CultureFeedUserIdentityResolver $userIdentityResolver
     */
    public function __construct(CultureFeedUserIdentityResolver $userIdentityResolver)
    {
        $this->userIdentityResolver = $userIdentityResolver;
    }

    /**
     * @inheritdoc
     */
    public function byEmail(EmailAddress $email)
    {
        $user = $this->userIdentityResolver->getUserByEmail($email);

        if (!is_null($user)) {
            return $user->getUserId();
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function byNick(StringLiteral $nick)
    {
        $user = $this->userIdentityResolver->getUserByNick($nick);

        if (!is_null($user)) {
            return $user->getUserId();
        } else {
            return null;
        }
    }
}
