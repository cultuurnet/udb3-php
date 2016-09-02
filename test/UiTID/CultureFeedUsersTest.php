<?php

namespace CultuurNet\UDB3\UiTID;

use CultuurNet\UDB3\User\CultureFeedUserIdentityDetailsFactory;
use CultuurNet\UDB3\User\CultureFeedUserIdentityResolver;
use ValueObjects\String\String;
use ValueObjects\Web\EmailAddress;

class CultureFeedUsersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CultureFeedUsers
     */
    private $users;

    /**
     * @var \ICultureFeed|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cultureFeed;

    /**
     * @var \CultureFeed_SearchUser
     */
    private $user;

    public function setUp()
    {
        $this->cultureFeed = $this->getMock(\ICultureFeed::class);
        $this->users = new CultureFeedUsers(
            new CultureFeedUserIdentityResolver(
                $this->cultureFeed,
                new CultureFeedUserIdentityDetailsFactory()
            )
        );

        $this->user = $user = new \CultureFeed_SearchUser();
        $this->user->id = 'abc';
        $this->user->nick = 'johndoe';
        $this->user->mbox = 'johndoe@example.com';
    }

    /**
     * @test
     */
    public function it_can_retrieve_a_user_id_by_its_nick_name()
    {
        $byNick = new String('johndoe');

        $expectedQuery = new \CultureFeed_SearchUsersQuery();
        $expectedQuery->nick = $byNick->toNative();

        $resultSet = new \CultureFeed_ResultSet(1, [$this->user]);

        $this->cultureFeed->expects($this->once())
            ->method('searchUsers')
            ->with($expectedQuery)
            ->willReturn($resultSet);

        $id = $this->users->byNick($byNick);

        $this->assertEquals(new String('abc'), $id);
    }

    /**
     * @test
     */
    public function it_returns_null_if_the_nick_name_of_the_found_user_does_not_match_the_given_nick_name()
    {
        $byNick = new String('*doe');

        $expectedQuery = new \CultureFeed_SearchUsersQuery();
        $expectedQuery->nick = $byNick->toNative();

        $resultSet = new \CultureFeed_ResultSet(1, [$this->user]);

        $this->cultureFeed->expects($this->once())
            ->method('searchUsers')
            ->with($expectedQuery)
            ->willReturn($resultSet);

        $id = $this->users->byNick($byNick);

        $this->assertNull($id);
    }

    /**
     * @test
     */
    public function it_can_retrieve_a_user_id_by_its_email_address()
    {
        $byEmail = new EmailAddress('johndoe@example.com');

        $expectedQuery = new \CultureFeed_SearchUsersQuery();
        $expectedQuery->mbox = $byEmail->toNative();
        $expectedQuery->mboxIncludePrivate = true;

        $resultSet = new \CultureFeed_ResultSet(1, [$this->user]);

        $this->cultureFeed->expects($this->once())
            ->method('searchUsers')
            ->with($expectedQuery)
            ->willReturn($resultSet);

        $id = $this->users->byEmail($byEmail);

        $this->assertEquals(new String('abc'), $id);
    }

    /**
     * @test
     */
    public function it_returns_null_if_the_email_address_of_the_found_user_does_not_match_the_given_email_address()
    {
        $byEmail = new EmailAddress('*@example.com');

        $expectedQuery = new \CultureFeed_SearchUsersQuery();
        $expectedQuery->mbox = $byEmail->toNative();
        $expectedQuery->mboxIncludePrivate = true;

        $resultSet = new \CultureFeed_ResultSet(1, [$this->user]);

        $this->cultureFeed->expects($this->once())
            ->method('searchUsers')
            ->with($expectedQuery)
            ->willReturn($resultSet);

        $id = $this->users->byEmail($byEmail);

        $this->assertNull($id);
    }

    /**
     * @test
     */
    public function it_returns_null_when_the_user_can_not_be_found()
    {
        $this->cultureFeed->expects($this->any())
            ->method('searchUsers')
            ->willReturn(new \CultureFeed_ResultSet());

        $this->assertNull($this->users->byEmail(new EmailAddress('johndoe@example.com')));
        $this->assertNull($this->users->byNick(new String('johndoe')));
    }
}
