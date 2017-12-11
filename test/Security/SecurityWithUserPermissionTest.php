<?php

namespace CultuurNet\UDB3\Security;

use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;
use CultuurNet\UDB3\Offer\Security\Permission\PermissionVoterInterface;
use CultuurNet\UDB3\Place\Commands\UpdateFacilities;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\StringLiteral\StringLiteral;

class SecurityWithUserPermissionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SecurityInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $security;

    /**
     * @var UserIdentificationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userIdentification;

    /**
     * @var PermissionVoterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionVoter;

    /**
     * @var CommandFilterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commandFilter;

    /**
     * @var SecurityWithUserPermission
     */
    private $securityWithUserPermission;

    protected function setUp()
    {
        $this->security = $this->createMock(SecurityInterface::class);

        $this->userIdentification = $this->createMock(UserIdentificationInterface::class);

        $this->permissionVoter = $this->createMock(PermissionVoterInterface::class);

        $this->commandFilter = $this->createMock(CommandFilterInterface::class);

        $this->securityWithUserPermission = new SecurityWithUserPermission(
            $this->security,
            $this->userIdentification,
            $this->permissionVoter,
            $this->commandFilter
        );
    }

    /**
     * @test
     */
    public function it_delegates_to_permission_voter_when_command_matches()
    {
        /** @var AuthorizableCommandInterface|\PHPUnit_Framework_MockObject_MockObject $command */
        $command = $this->createMock(AuthorizableCommandInterface::class);
        $command->expects($this->once())
            ->method('getPermission')
            ->willReturn(Permission::VOORZIENINGEN_BEWERKEN());

        $userId = new StringLiteral('315820fe-9ba4-43b3-b567-5a1e43ec430d');
        $this->userIdentification->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $this->permissionVoter->expects($this->once())
            ->method('isAllowed')
            ->with(
                Permission::VOORZIENINGEN_BEWERKEN(),
                new StringLiteral(''),
                $userId
            )
            ->willReturn(
                true
            );

        $this->commandFilter->expects($this->once())
            ->method('matches')
            ->with($command)
            ->willReturn(true);

        $this->assertTrue(
            $this->securityWithUserPermission->isAuthorized(
                $command
            )
        );
    }

    /**
     * @test
     */
    public function it_delegates_to_parent_when_command_does_not_match()
    {
        /** @var AuthorizableCommandInterface $command */
        $command = $this->createMock(AuthorizableCommandInterface::class);

        $this->security->expects($this->once())
            ->method('isAuthorized')
            ->with($command)
            ->willReturn(true);

        $this->commandFilter->expects($this->once())
            ->method('matches')
            ->with($command)
            ->willReturn(false);

        $this->assertTrue(
            $this->securityWithUserPermission->isAuthorized($command)
        );
    }
}
