<?php

namespace CultuurNet\UDB3\Offer\Security;

use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;
use CultuurNet\UDB3\Offer\Security\Permission\PermissionVoterInterface;
use CultuurNet\UDB3\Place\Commands\UpdateFacilities;
use CultuurNet\UDB3\Security\SecurityInterface;
use CultuurNet\UDB3\Security\UserIdentificationInterface;
use ValueObjects\StringLiteral\StringLiteral;

class SecurityWithFacilityPermissionTest extends \PHPUnit_Framework_TestCase
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
     * @var SecurityWithFacilityPermission
     */
    private $securityWithFacilityPermission;

    protected function setUp()
    {
        $this->security = $this->createMock(SecurityInterface::class);

        $this->userIdentification = $this->createMock(UserIdentificationInterface::class);

        $this->permissionVoter = $this->createMock(PermissionVoterInterface::class);

        $this->securityWithFacilityPermission = new SecurityWithFacilityPermission(
            $this->security,
            $this->userIdentification,
            $this->permissionVoter
        );
    }

    /**
     * @test
     */
    public function it_delegates_to_permission_voter_when_facility_command()
    {
        $updateFacilities = new UpdateFacilities(
            '600ccde6-0f43-46ec-912c-b0f88a2d7bf9',
            [
                'facility1',
                'facility2',
            ]
        );

        $userId = new StringLiteral('315820fe-9ba4-43b3-b567-5a1e43ec430d');
        $this->userIdentification->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $this->permissionVoter->expects($this->once())
            ->method('isAllowed')
            ->with(
                $updateFacilities->getPermission(),
                new StringLiteral(''),
                $userId
            )
            ->willReturn(
                true
            );

        $this->assertTrue(
            $this->securityWithFacilityPermission->isAuthorized(
                $updateFacilities
            )
        );
    }

    /**
     * @test
     */
    public function it_delegates_to_parent_when_not_a_facility_command()
    {
        $command = $this->createMock(AuthorizableCommandInterface::class);

        $this->security->expects($this->once())
            ->method('isAuthorized')
            ->with($command)
            ->willReturn(true);

        $this->assertTrue(
            $this->securityWithFacilityPermission->isAuthorized($command)
        );
    }
}
