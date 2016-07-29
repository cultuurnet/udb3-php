<?php

namespace CultuurNet\UDB3\UiTID;

use CultuurNet\UDB3\User\UserIdentityDetails;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\EmailAddress;

interface UsersInterface
{
    /**
     * @param StringLiteral $userId
     * @return UserIdentityDetails
     */
    public function getUserById(StringLiteral $userId);

    /**
     * @param EmailAddress $email
     * @return UserIdentityDetails
     */
    public function getUserByEmail(EmailAddress $email);

    /**
     * @param StringLiteral $nick
     * @return UserIdentityDetails
     */
    public function getUserByNick(StringLiteral $nick);

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
