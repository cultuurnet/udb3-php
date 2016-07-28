<?php

namespace CultuurNet\UDB3\UiTID;

use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\EmailAddress;

interface UsersInterface
{
    /**
     * @param EmailAddress $email
     * @return StringLiteral|null
     *   Id of the user with the given e-mail address or null if not found.
     */
    public function byEmail(EmailAddress $email);

    /**
     * @param StringLiteral $nick
     * @return StringLiteral|null
     *   Id of the user with the given nick or null if not found.
     */
    public function byNick(StringLiteral $nick);
}
