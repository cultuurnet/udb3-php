<?php

namespace CultuurNet\UDB3\UiTID;

use CultuurNet\UDB3\User\CultureFeedUserIdentityDetailsFactoryInterface;
use CultuurNet\UDB3\User\UserIdentityDetails;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\EmailAddress;

class CultureFeedUsers implements UsersInterface
{
    /**
     * @var \ICultureFeed
     */
    private $cultureFeed;

    /**
     * @var CultureFeedUserIdentityDetailsFactoryInterface
     */
    private $userIdentityDetailsFactory;

    /**
     * @param \ICultureFeed $cultureFeed
     * @param CultureFeedUserIdentityDetailsFactoryInterface $userIdentityDetailsFactory
     */
    public function __construct(
        \ICultureFeed $cultureFeed,
        CultureFeedUserIdentityDetailsFactoryInterface $userIdentityDetailsFactory
    ) {
        $this->cultureFeed = $cultureFeed;
        $this->userIdentityDetailsFactory = $userIdentityDetailsFactory;
    }

    /**
     * @inheritdoc
     */
    public function getUserById(StringLiteral $userId)
    {
        $query = new \CultureFeed_SearchUsersQuery();
        $query->userId = $userId->toNative();

        $user = $this->searchSingleUser($query);

        if ($user && $user->getUserId()->toNative() == $userId->toNative()) {
            return $user;
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function getUserByEmail(EmailAddress $email)
    {
        $query = new \CultureFeed_SearchUsersQuery();
        $query->mbox = $email->toNative();
        $query->mboxIncludePrivate = true;

        $user = $this->searchSingleUser($query);

        // Given e-mail address could contain a wildcard (eg. *@cultuurnet.be),
        // so we should make sure the emails are exactly the same, otherwise
        // we're just returning the first user that matches the wildcard which
        // is not intended.
        if ($user && $user->getEmailAddress()->toNative() === $email->toNative()) {
            return $user;
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function getUserByNick(StringLiteral $nick)
    {
        $query = new \CultureFeed_SearchUsersQuery();
        $query->nick = $nick->toNative();

        $user = $this->searchSingleUser($query);

        // Given nick could contain a wildcard (eg. *somepartofnick*), so we
        // should make sure the nicks are exactly the same, otherwise we're
        // just returning the first user that matches the wildcard which is not
        // intended.
        if ($user && $user->getUserName()->toNative() === $nick->toNative()) {
            return $user;
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function byEmail(EmailAddress $email)
    {
        $user = $this->getUserByEmail($email);

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
        $user = $this->getUserByNick($nick);

        if (!is_null($user)) {
            return $user->getUserId();
        } else {
            return null;
        }
    }

    /**
     * @param \CultureFeed_SearchUsersQuery $query
     * @return UserIdentityDetails|null
     */
    private function searchSingleUser(\CultureFeed_SearchUsersQuery $query)
    {
        /** @var \CultureFeed_ResultSet $results */
        $results = $this->cultureFeed->searchUsers($query);

        /** @var \CultureFeed_SearchUser $user */
        $user = reset($results->objects);

        if ($user) {
            return $this->userIdentityDetailsFactory->fromCultureFeedUserSearchResult($user);
        } else {
            return null;
        }
    }
}
