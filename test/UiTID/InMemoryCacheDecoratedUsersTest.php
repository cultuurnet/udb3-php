<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UiTID;

use PHPUnit\Framework\TestCase;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\EmailAddress;

class InMemoryCacheDecoratedUsersTest extends TestCase
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
        $this->wrapped = $this->createMock(UsersInterface::class);
        $this->users = new InMemoryCacheDecoratedUsers($this->wrapped);
    }

    /**
     * @test
     */
    public function it_uses_cached_user_id_when_retrieving_by_nick()
    {
        $userId = new StringLiteral('abc');
        $nick = new StringLiteral('johndoe');

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
        $userId = new StringLiteral('abc');
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
