<?php

namespace CultuurNet\UDB3\UiTID;

use Psr\Log\LoggerInterface;
use ValueObjects\Exception\InvalidNativeArgumentException;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\EmailAddress;

class CdbXmlCreatedByToUserIdResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UsersInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $users;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var CdbXmlCreatedByToUserIdResolver
     */
    private $resolver;

    public function setUp()
    {
        $this->users = $this->createMock(UsersInterface::class);
        $this->resolver = new CdbXmlCreatedByToUserIdResolver($this->users);

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->resolver->setLogger($this->logger);
    }

    /**
     * @test
     */
    public function it_first_tries_to_resolve_created_by_as_a_uuid()
    {
        $createdBy = new StringLiteral('4eaf3516-342f-4c28-a2ce-80a0c6332f11');

        $actualUserId = $this->resolver->resolveCreatedByToUserId($createdBy);

        $this->assertEquals($createdBy, $actualUserId);
    }

    /**
     * @test
     */
    public function it_logs_when_created_by_is_not_a_uuid()
    {
        $createdBy = new StringLiteral('acf1c0f-30d-3ef-e7b-cd4b7676206');

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                'The provided createdByIdentifier acf1c0f-30d-3ef-e7b-cd4b7676206 is not a UUID.',
                [
                    'exception' => new InvalidNativeArgumentException(
                        $createdBy,
                        [
                            'UUID string',
                        ]
                    ),
                ]
            );

        $actualUserId = $this->resolver->resolveCreatedByToUserId($createdBy);

        $this->assertNull($actualUserId);
    }

    /**
     * @test
     */
    public function it_then_tries_to_resolve_createdby_as_an_email_address()
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
