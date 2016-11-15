<?php

namespace CultuurNet\UDB3\Security;

use ValueObjects\String\String as StringLiteral;

class CultureFeedUserIdentification implements UserIdentificationInterface
{
    /**
     * @var \CultureFeed_User
     */
    private $cultureFeedUser;

    /**
     * @var \string[]
     */
    private $permissionList;

    /**
     * CultureFeedUserIdentification constructor.
     * @param \CultureFeed_User $cultureFeedUser
     * @param \string[] $permissionList
     */
    public function __construct(
        \CultureFeed_User $cultureFeedUser,
        array $permissionList
    ) {
        $this->cultureFeedUser = $cultureFeedUser;
        $this->permissionList = $permissionList;
    }


    /**
     * @return bool
     */
    public function isGodUser()
    {
        return in_array(
            $this->cultureFeedUser->id,
            $this->permissionList['allow_all']
        );
    }

    /**
     * @return StringLiteral
     */
    public function getId()
    {
        // The default constructor of CultureFeed_User sets all data members to null.
        // This would result in a crash of the StringLiteral constructor for the id.
        // Solved by creating the StringLiteral with an empty string instead of null.
        if (empty($this->cultureFeedUser->id)) {
            return new StringLiteral('');
        } else {
            return new StringLiteral($this->cultureFeedUser->id);
        }
    }
}
