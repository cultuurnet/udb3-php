<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UiTID;

use ValueObjects\String\String;
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

        return $this->searchSingleUser($query);
    }

    /**
     * @param \CultureFeed_SearchUsersQuery $query
     * @return string|null
     */
    private function searchSingleUser(\CultureFeed_SearchUsersQuery $query)
    {
        /** @var \CultureFeed_ResultSet $results */
        $results = $this->cultureFeed->searchUsers($query);

        /** @var \CultureFeed_SearchUser $user */
        $user = reset($results->objects);

        if ($user) {
            return new String($user->id);
        }

        return;
    }

    /**
     * @inheritdoc
     */
    public function byNick(String $nick)
    {
        $query = new \CultureFeed_SearchUsersQuery();
        $query->nick = $nick->toNative();

        return $this->searchSingleUser($query);
    }
}
