<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UiTID;

use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\EmailAddress;

class CultureFeedUsers implements UsersInterface
{
    /**
     * @var \ICultureFeed
     */
    private $cultureFeed;

    /**
     * @param \ICultureFeed $cultureFeed
     */
    public function __construct(\ICultureFeed $cultureFeed)
    {
        $this->cultureFeed = $cultureFeed;
    }

    /**
     * @inheritdoc
     */
    public function byEmail(EmailAddress $email)
    {
        $query = new \CultureFeed_SearchUsersQuery();
        $query->mbox = $email->toNative();
        $query->mboxIncludePrivate = true;

        $user = $this->searchSingleUser($query);

        // Given e-mail address could contain a wildcard (eg. *@cultuurnet.be),
        // so we should make sure the emails are exactly the same, otherwise
        // we're just returning the first user that matches the wildcard which
        // is not intended.
        if ($user && $user->mbox === $email->toNative()) {
            return new StringLiteral($user->id);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function byNick(StringLiteral $nick)
    {
        $query = new \CultureFeed_SearchUsersQuery();
        $query->nick = $nick->toNative();

        $user = $this->searchSingleUser($query);

        // Given nick could contain a wildcard (eg. *somepartofnick*), so we
        // should make sure the nicks are exactly the same, otherwise we're
        // just returning the first user that matches the wildcard which is not
        // intended.
        if ($user && $user->nick === $nick->toNative()) {
            return new StringLiteral($user->id);
        }

        return null;
    }

    /**
     * @param \CultureFeed_SearchUsersQuery $query
     * @return \CultureFeed_SearchUser|null
     */
    private function searchSingleUser(\CultureFeed_SearchUsersQuery $query)
    {
        /** @var \CultureFeed_ResultSet $results */
        $results = $this->cultureFeed->searchUsers($query);

        /** @var \CultureFeed_SearchUser $user */
        $user = reset($results->objects);

        return $user ? $user : null;
    }
}
