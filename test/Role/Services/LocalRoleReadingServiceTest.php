<?php

namespace CultuurNet\UDB3\Role\Services;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;

class LocalRoleReadingServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleReadRepository;

    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rolePermissionsReadRepository;

    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleLabelsReadRepository;

    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleUsersPermissionsReadRepository;

    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userRolesPermissionsReadRepository;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleWriteRepository;

    /**
     * @var IriGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $iriGenerator;

    /**
     * @var LocalRoleReadingService
     */
    private $readingService;

    public function setUp()
    {
        $this->roleReadRepository = $this->getMock(DocumentRepositoryInterface::class);
        $this->roleWriteRepository = $this->getMock(RepositoryInterface::class);
        $this->iriGenerator = $this->getMock(IriGeneratorInterface::class);
        $this->rolePermissionsReadRepository = $this->getMock(DocumentRepositoryInterface::class);
        $this->roleLabelsReadRepository = $this->getMock(DocumentRepositoryInterface::class);
        $this->roleUsersPermissionsReadRepository = $this->getMock(DocumentRepositoryInterface::class);
        $this->userRolesPermissionsReadRepository = $this->getMock(DocumentRepositoryInterface::class);

        $this->readingService = new LocalRoleReadingService(
            $this->roleReadRepository,
            $this->roleWriteRepository,
            $this->iriGenerator,
            $this->rolePermissionsReadRepository,
            $this->roleLabelsReadRepository,
            $this->roleUsersPermissionsReadRepository,
            $this->userRolesPermissionsReadRepository
        );
    }

    /**
     * @test
     */
    public function it_returns_the_details_of_a_role()
    {
        $roleId = 'da114bb4-42bc-11e6-beb8-9e71128cae77';
        $document = new JsonDocument('da114bb4-42bc-11e6-beb8-9e71128cae77');
        $json = $document->getBody();
        $json->{'@id'} = $roleId;
        $json->name = 'administrator';
        $json->query = 'category_flandersregion_name:"Regio Brussel"';
        $expectedRole = $document->withBody($json);

        $this->roleReadRepository->expects($this->once())
            ->method('get')
            ->with($roleId)
            ->willReturn($expectedRole);

        $role = $this->readingService->getEntity($roleId);

        $this->assertEquals($expectedRole->getRawBody(), $role);
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

        $this->rolePermissionsReadRepository->expects($this->once())
            ->method('get')
            ->with($roleId)
            ->willReturn($expectedPermissions);

        $permissions = $this->readingService->getPermissionsByRoleUuid($roleId);

        $this->assertEquals($expectedPermissions, $permissions);
    }

    /**
     * @test
     */
    public function it_returns_the_labels_of_a_role()
    {
        $roleId = new UUID();

        $expectedLabels = (new JsonDocument($roleId))
            ->withBody(
                json_encode([])
            );

        $this->roleLabelsReadRepository->expects($this->once())
            ->method('get')
            ->with($roleId)
            ->willReturn($expectedLabels);

        $actualLabels = $this->readingService->getLabelsByRoleUuid($roleId);

        $this->assertEquals($expectedLabels, $actualLabels);
    }
}
