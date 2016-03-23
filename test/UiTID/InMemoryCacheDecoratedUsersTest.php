<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UiTID;

use ValueObjects\String\String;
use ValueObjects\Web\EmailAddress;

class InMemoryCacheDecoratedUsersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UsersInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wrapped;

    /**
     * @var InMemoryCacheDecoratedUsers
     */
    private $users;

    public function setUp()
    {
        $this->wrapped = $this->getMock(UsersInterface::class);
        $this->users = new InMemoryCacheDecoratedUsers($this->wrapped);
    }

    /**
     * @test
     */
    public function it_uses_cached_user_id_when_retrieving_by_nick()
    {
        $userId = new String('abc');
        $nick = new String('johndoe');

        $this->wrapped->expects($this->once())
            ->method('byNick')
            ->with($nick)
            ->willReturn($userId);

        foreach (range(0, 1) as $iteration) {
            $actualUserId = $this->users->byNick($nick);
            $this->assertEquals($userId, $actualUserId);
        }
    }

    /**
     * @test
     */
    public function it_uses_cached_user_id_when_retrieving_by_mail()
    {
        $userId = new String('abc');
        $email = new EmailAddress('johndoe@example.com');

        $this->wrapped->expects($this->once())
            ->method('byEmail')
            ->with($email)
            ->willReturn($userId);

        foreach (range(0, 1) as $iteration) {
            $actualUserId = $this->users->byEmail($email);
            $this->assertEquals($userId, $actualUserId);
        }
    }
}
