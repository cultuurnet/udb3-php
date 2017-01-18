<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UiTID;

use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\EmailAddress;

class CdbXmlCreatedByToUserIdResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UsersInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $users;

    /**
     * @var CdbXmlCreatedByToUserIdResolver
     */
    private $resolver;

    public function setUp()
    {
        $this->users = $this->createMock(UsersInterface::class);
        $this->resolver = new CdbXmlCreatedByToUserIdResolver($this->users);
    }

    /**
     * @test
     */
    public function it_first_tries_to_resolve_createdby_as_an_email_address()
    {
        $createdBy = new StringLiteral('johndoe@example.com');
        $userId = new StringLiteral('abc');

        $this->users->expects($this->once())
            ->method('byEmail')
            ->with(new EmailAddress('johndoe@example.com'))
            ->willReturn($userId);

        $actualUserId = $this->resolver->resolveCreatedByToUserId($createdBy);

        $this->assertEquals($userId, $actualUserId);
    }

    /**
     * @test
     */
    public function it_falls_back_to_resolving_createdby_as_a_nick_name()
    {
        $createdBy = new StringLiteral('johndoe');
        $userId = new StringLiteral('abc');

        $this->users->expects($this->never())
            ->method('byEmail');

        $this->users->expects($this->once())
            ->method('byNick')
            ->with($createdBy)
            ->willReturn($userId);

        $actualUserId = $this->resolver->resolveCreatedByToUserId($createdBy);

        $this->assertEquals($userId, $actualUserId);
    }

    /**
     * @test
     */
    public function it_returns_null_when_user_id_not_resolved()
    {
        $createdBy = new StringLiteral('johndoe');

        $this->users->expects($this->once())
            ->method('byNick')
            ->willReturn(null);

        $userId = $this->resolver->resolveCreatedByToUserId($createdBy);

        $this->assertNull($userId);
    }
}
