<?php

namespace CultuurNet\UDB3\Role\ReadModel\Constraints\Doctrine;

use CultuurNet\UDB3\DBALTestConnectionTrait;
use CultuurNet\UDB3\Role\ReadModel\Constraints\UserConstraintsReadRepositoryInterface;
use CultuurNet\UDB3\Role\ReadModel\Constraints\Doctrine\SchemaConfigurator as ConstraintSchemaConfigurator;
use CultuurNet\UDB3\Role\ReadModel\Permissions\Doctrine\SchemaConfigurator as PermissionSchemaConfigurator;
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
     * @var StringLiteral
     */
    private $roleConstraintTableName;

    /**
     * @var UserConstraintsReadRepositoryInterface
     */
    private $userConstraintsReadRepository;

    protected function setUp()
    {
        $this->roleIds = [new UUID(), new UUID(), new UUID()];

        $this->userRolesTableName = new StringLiteral('user_roles');
        $this->rolePermissionsTableName = new StringLiteral('role_permissions');
        $this->roleConstraintTableName = new StringLiteral('role_constraint');

        $permissionSchemaConfigurator = new PermissionSchemaConfigurator(
            $this->userRolesTableName,
            $this->rolePermissionsTableName
        );
        $permissionSchemaConfigurator->configure(
            $this->getConnection()->getSchemaManager()
        );

        $constraintSchemaConfigurator = new ConstraintSchemaConfigurator(
            $this->roleConstraintTableName
        );
        $constraintSchemaConfigurator->configure(
            $this->getConnection()->getSchemaManager()
        );

        $this->userConstraintsReadRepository = new UserConstraintsReadRepository(
            $this->getConnection(),
            $this->userRolesTableName,
            $this->rolePermissionsTableName,
            $this->roleConstraintTableName
        );

        $this->seedUserRoles();
        $this->seedRolePermissions();
        $this->seedRoleConstraint();
    }

    /**
     * @test
     */
    public function it_returns_constraints_for_a_certain_user_and_permission()
    {
        $constraints = $this->userConstraintsReadRepository->getByUserAndPermission(
            new StringLiteral('user1'),
            Permission::AANBOD_MODEREREN()
        );

        $expectedConstraints = [
            new StringLiteral('zipCode:1000'),
            new StringLiteral('zipCode:3000')
        ];

        $this->assertEquals(
            $expectedConstraints,
            $constraints,
            'Constraints do not match expected!',
            0.0,
            0,
            true
        );
    }

    /**
     * @test
     */
    public function it_returns_empty_array_for_a_missing_user()
    {
        $constraints = $this->userConstraintsReadRepository->getByUserAndPermission(
            new StringLiteral('user3'),
            Permission::AANBOD_MODEREREN()
        );

        $this->assertEmpty($constraints);
    }

    /**
     * @test
     */
    public function it_returns_empty_array_for_a_missing_permission()
    {
        $constraints = $this->userConstraintsReadRepository->getByUserAndPermission(
            new StringLiteral('user1'),
            Permission::AANBOD_INVOEREN()
        );

        $this->assertEmpty($constraints);
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

    private function seedRoleConstraint()
    {
        $this->insertRoleConstraint($this->roleIds[0], new StringLiteral('zipCode:1000'));
        $this->insertRoleConstraint($this->roleIds[1], new StringLiteral('zipCode:2000'));
        $this->insertRoleConstraint($this->roleIds[2], new StringLiteral('zipCode:3000'));
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
                PermissionSchemaConfigurator::USER_ID_COLUMN => $userId->toNative(),
                PermissionSchemaConfigurator::ROLE_ID_COLUMN => $roleId->toNative()
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
                PermissionSchemaConfigurator::ROLE_ID_COLUMN => $roleId->toNative(),
                PermissionSchemaConfigurator::PERMISSION_COLUMN => $permission->toNative()
            ]
        );
    }

    /**
     * @param UUID $roleId
     * @param StringLiteral $constraint
     */
    private function insertRoleConstraint(UUID $roleId, StringLiteral $constraint)
    {
        $this->getConnection()->insert(
            $this->roleConstraintTableName,
            [
                PermissionSchemaConfigurator::ROLE_ID_COLUMN => $roleId->toNative(),
                ConstraintSchemaConfigurator::CONSTRAINT_COLUMN => $constraint->toNative()
            ]
        );
    }
}
