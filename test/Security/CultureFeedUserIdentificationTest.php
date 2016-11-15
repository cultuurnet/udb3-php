<?php

namespace CultuurNet\UDB3\Security;

use ValueObjects\String\String as StringLiteral;

class CultureFeedUserIdentificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \CultureFeed_User
     */
    private $cultureFeedUser;

    /**
     * @var \string[][]
     */
    private $permissionList;

    /**
     * @var CultureFeedUserIdentification
     */
    private $cultureFeedUserIdentification;

    protected function setUp()
    {
        $this->cultureFeedUser = new \CultureFeed_User();
        $this->cultureFeedUser->id = 'godUserId';

        $this->permissionList['allow_all'] = ['godUserId', 'otherGodUserId'];

        $this->cultureFeedUserIdentification = new CultureFeedUserIdentification(
            $this->cultureFeedUser,
            $this->permissionList
        );
    }

    /**
     * @test
     */
    public function it_can_determine_if_a_user_is_a_god_user()
    {
        $this->assertTrue($this->cultureFeedUserIdentification->isGodUser());
    }

    /**
     * @test
     */
    public function it_can_determine_if_a_user_is_not_a_god_user()
    {
        $cultureFeedUser = new \CultureFeed_User();
        $cultureFeedUser->id = 'normalUserId';

        $cultureFeedUserIdentification = new CultureFeedUserIdentification(
            $cultureFeedUser,
            $this->permissionList
        );

        $this->assertFalse($cultureFeedUserIdentification->isGodUser());
    }

    /**
     * @test
     */
    public function it_returns_the_id_of_a_user()
    {
        $this->assertEquals(
            new StringLiteral('godUserId'),
            $this->cultureFeedUserIdentification->getId()
        );
    }

    /**
     * @test
     */
    public function it_has_an_empty_id_for_empty_culture_feed_user()
    {
        $cultureFeedUserIdentification = new CultureFeedUserIdentification(
            new \CultureFeed_User(),
            $this->permissionList
        );

        $this->assertEquals(
            new StringLiteral(''),
            $cultureFeedUserIdentification->getId()
        );
    }

    /**
     * @test
     */
    public function it_returns_false_for_god_user_of_an_empty_culture_feed_user()
    {
        $cultureFeedUserIdentification = new CultureFeedUserIdentification(
            new \CultureFeed_User(),
            $this->permissionList
        );

        $this->assertFalse($cultureFeedUserIdentification->isGodUser());
    }
}
