<?php

namespace CultuurNet\UDB3\User;

use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\EmailAddress;

interface UserIdentityResolverInterface
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
}
