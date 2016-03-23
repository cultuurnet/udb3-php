<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UiTID;

use ValueObjects\String\String;
use ValueObjects\Web\EmailAddress;

interface UsersInterface
{
    /**
     * @param EmailAddress $email
     * @return String Id of the user with the given e-mail address.
     */
    public function byEmail(EmailAddress $email);

    /**
     * @param String $nick
     * @return String Id of the user with the given nick.
     */
    public function byNick(String $nick);
}
