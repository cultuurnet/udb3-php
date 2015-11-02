<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UiTID;

use ValueObjects\String\String;
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
        $this->users = $this->getMock(UsersInterface::class);
        $this->resolver = new CdbXmlCreatedByToUserIdResolver($this->users);
    }

    /**
     * @test
     */
    public function it_first_tries_to_resolve_createdby_as_an_email_address()
    {
        $createdBy = new String('johndoe@example.com');
        $userId = new String('abc');

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
        $createdBy = new String('johndoe');
        $userId = new String('abc');

        $this->users->expects($this->never())
            ->method('byEmail');

        $this->users->expects($this->once())
            ->method('byNick')
            ->with($createdBy)
            ->willReturn($userId);

        $actualUserId = $this->resolver->resolveCreatedByToUserId($createdBy);

        $this->assertEquals($userId, $actualUserId);
    }
}
