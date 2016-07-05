<?php

namespace CultuurNet\UDB3\Role\Services;

use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;

class LocalRoleReadingServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleRepository;

    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rolePermissionsRepository;

    /**
     * @var LocalRoleReadingService
     */
    private $readingService;

    public function setUp()
    {
        $this->roleRepository = $this->getMock(
            DocumentRepositoryInterface::class
        );

        $this->rolePermissionsRepository = $this->getMock(
            DocumentRepositoryInterface::class
        );

        $this->readingService = new LocalRoleReadingService(
            $this->roleRepository,
            $this->rolePermissionsRepository
        );
    }

    /**
     * @test
     */
    public function it_returns_the_details_of_a_role()
    {
        $roleId = new UUID('da114bb4-42bc-11e6-beb8-9e71128cae77');
        $document = new JsonDocument('da114bb4-42bc-11e6-beb8-9e71128cae77');
        $json = $document->getBody();
        $json->{'@id'} = $roleId->toNative();
        $json->name = 'administrator';
        $json->query = 'category_flandersregion_name:"Regio Brussel"';
        $expectedRole = $document->withBody($json);

        $this->roleRepository->expects($this->once())
            ->method('get')
            ->with($roleId)
            ->willReturn($expectedRole);

        $role = $this->readingService->getByUuid($roleId);

        $this->assertEquals($expectedRole, $role);
    }

    /**
     * @test
     */
    public function it_returns_the_permissions_of_a_role()
    {
        /** @var Permission $permission1 */
        $permission1 = Permission::AANBOD_INVOEREN();
        /** @var Permission $permission2 */
        $permission2 = Permission::AANBOD_BEWERKEN();
        $roleId = new UUID('da114bb4-42bc-11e6-beb8-9e71128cae77');
        $document = new JsonDocument('da114bb4-42bc-11e6-beb8-9e71128cae77');
        $json = $document->getBody();
        $json->{'@id'} = $roleId->toNative();
        $json->permissions[$permission1->getName()] = $permission1->getValue();
        $json->permissions[$permission2->getName()] = $permission2->getValue();
        $expectedPermissions = $document->withBody($json);

        $this->rolePermissionsRepository->expects($this->once())
            ->method('get')
            ->with($roleId)
            ->willReturn($expectedPermissions);

        $permissions = $this->readingService->getPermissionsByRoleUuid($roleId);

        $this->assertEquals($expectedPermissions, $permissions);
    }
}
