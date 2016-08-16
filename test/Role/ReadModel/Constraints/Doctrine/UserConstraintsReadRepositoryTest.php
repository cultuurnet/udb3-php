<?php

namespace CultuurNet\UDB3\Role\ReadModel\Constraints\Doctrine;

use CultuurNet\UDB3\DBALTestConnectionTrait;
use CultuurNet\UDB3\Role\ReadModel\Constraints\UserConstraintsReadRepositoryInterface;
use CultuurNet\UDB3\Role\ReadModel\Permissions\Doctrine\SchemaConfigurator;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class UserConstraintsReadRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use DBALTestConnectionTrait;

    /**
     * @var UUID[]
     */
    private $roleIds;

    /**
     * @var StringLiteral
     */
    private $userRolesTableName;

    /**
     * @var StringLiteral
     */
    private $rolePermissionsTableName;

    /**
     * @var UserConstraintsReadRepositoryInterface
     */
    private $userConstraintsReadRepository;

    protected function setUp()
    {
        $this->roleIds = [new UUID(), new UUID(), new UUID()];

        $this->userRolesTableName = new StringLiteral('user_roles');
        $this->rolePermissionsTableName = new StringLiteral('role_permissions');

        $schemaConfigurator = new SchemaConfigurator(
            $this->userRolesTableName,
            $this->rolePermissionsTableName
        );
        $schemaConfigurator->configure(
            $this->getConnection()->getSchemaManager()
        );

        $this->userConstraintsReadRepository = new UserConstraintsReadRepository(
            $this->getConnection(),
            $this->userRolesTableName,
            $this->rolePermissionsTableName
        );

        $this->seedUserRoles();
        $this->seedRolePermissions();
    }

    /**
     * @test
     */
    public function it_returns_constraints_for_a_certain_user_and_permission()
    {
        $roles = $this->userConstraintsReadRepository->getByUserAndPermission(
            new StringLiteral('user1'),
            Permission::AANBOD_MODEREREN()
        );

        // TODO: Needs to be a list of constraints.
        $expectedRoles = [
            new StringLiteral($this->roleIds[0]->toNative()),
            new StringLiteral($this->roleIds[2]->toNative())
        ];

        $this->assertEquals(
            $expectedRoles,
            $roles,
            'Constraints do not match expected!',
            0.0,
            0,
            true
        );
    }

    private function seedUserRoles()
    {
        $this->insertUserRole(new StringLiteral('user1'), $this->roleIds[0]);
        $this->insertUserRole(new StringLiteral('user1'), $this->roleIds[1]);
        $this->insertUserRole(new StringLiteral('user1'), $this->roleIds[2]);
        $this->insertUserRole(new StringLiteral('user2'), $this->roleIds[2]);
    }

    private function seedRolePermissions()
    {
        $this->insertUserPermission($this->roleIds[0], Permission::AANBOD_BEWERKEN());
        $this->insertUserPermission($this->roleIds[0], Permission::AANBOD_VERWIJDEREN());
        $this->insertUserPermission($this->roleIds[0], Permission::AANBOD_MODEREREN());

        $this->insertUserPermission($this->roleIds[1], Permission::LABELS_BEHEREN());
        $this->insertUserPermission($this->roleIds[1], Permission::GEBRUIKERS_BEHEREN());

        $this->insertUserPermission($this->roleIds[2], Permission::AANBOD_MODEREREN());
    }

    /**
     * @param StringLiteral $userId
     * @param UUID $roleId
     */
    private function insertUserRole(StringLiteral $userId, UUID $roleId)
    {
        $this->getConnection()->insert(
            $this->userRolesTableName,
            [
                SchemaConfigurator::USER_ID_COLUMN => $userId->toNative(),
                SchemaConfigurator::ROLE_ID_COLUMN => $roleId->toNative()
            ]
        );
    }

    /**
     * @param UUID $roleId
     * @param Permission $permission
     */
    private function insertUserPermission(UUID $roleId, Permission $permission)
    {
        $this->getConnection()->insert(
            $this->rolePermissionsTableName,
            [
                SchemaConfigurator::ROLE_ID_COLUMN => $roleId->toNative(),
                SchemaConfigurator::PERMISSION_COLUMN => $permission->toNative()
            ]
        );
    }
}
