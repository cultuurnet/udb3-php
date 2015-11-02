<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UiTID;

use Guzzle\Common\Validation\Email;
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

    public function setUp()
    {
        $this->cultureFeed = $this->getMock(\ICultureFeed::class);
        $this->users = new CultureFeedUsers($this->cultureFeed);
    }

    /**
     * @test
     */
    public function it_can_retrieve_a_user_id_by_its_nick_name()
    {
        $expectedQuery = new \CultureFeed_SearchUsersQuery();
        $expectedQuery->nick = 'johndoe';

        $user = new \CultureFeed_SearchUser();
        $user->id = 'abc';

        $resultSet = new \CultureFeed_ResultSet(1, [$user]);

        $this->cultureFeed->expects($this->once())
            ->method('searchUsers')
            ->with($expectedQuery)
            ->willReturn($resultSet);

        $id = $this->users->byNick(new String('johndoe'));

        $this->assertEquals(
            new String('abc'),
            $id
        );
    }

    /**
     * @test
     */
    public function it_can_retrieve_a_user_id_by_its_email_address()
    {
        $expectedQuery = new \CultureFeed_SearchUsersQuery();
        $expectedQuery->mbox = 'johndoe@example.com';
        $expectedQuery->mboxIncludePrivate = true;

        $user = new \CultureFeed_SearchUser();
        $user->id = 'abc';

        $resultSet = new \CultureFeed_ResultSet(1, [$user]);

        $this->cultureFeed->expects($this->once())
            ->method('searchUsers')
            ->with($expectedQuery)
            ->willReturn($resultSet);

        $id = $this->users->byEmail(new EmailAddress('johndoe@example.com'));

        $this->assertEquals(
            new String('abc'),
            $id
        );
    }

    /**
     * @test
     */
    public function it_returns_null_when_the_user_can_not_be_found()
    {
        $this->cultureFeed->expects($this->any())
            ->method('searchUsers')
            ->willReturn(new \CultureFeed_ResultSet());

        $this->assertNull(
            $this->users->byEmail(new EmailAddress('johndoe@example.com'))
        );

        $this->assertNull(
            $this->users->byNick(new String('johndoe'))
        );
    }
}
