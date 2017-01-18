<?php

namespace CultuurNet\UDB3\UiTID;

use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\EmailAddress;

/**
 * @deprecated
 *   Use CultuurNet\UDB3\User\UserIdentityResolverInterface instead.
 */
interface UsersInterface
{
    /**
     * @param EmailAddress $email
     * @return StringLiteral|null
     *   Id of the user with the given e-mail address or null if not found.
     *
     * @deprecated
     *   Use getUserByEmail() instead.
     */
    public function byEmail(EmailAddress $email);

    /**
     * @param StringLiteral $nick
     * @return StringLiteral|null
     *   Id of the user with the given nick or null if not found.
     *
     * @deprecated
     *   Use getUserByNick() instead.
     */
    public function byNick(StringLiteral $nick);
}
