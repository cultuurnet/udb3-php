<?php

namespace CultuurNet\UDB3\Role\ReadModel\Permissions\Doctrine;

use CultuurNet\UDB3\DBALTestConnectionTrait;
use CultuurNet\UDB3\Role\ReadModel\Permissions\UserPermissionsReadRepositoryInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use PHPUnit_Framework_TestCase;
use ValueObjects\String\String as StringLiteral;

class UserPermissionsReadRepositoryTest extends PHPUnit_Framework_TestCase
{
    use DBALTestConnectionTrait;

    /**
     * @var UserPermissionsReadRepositoryInterface
     */
    private $repository;

    /**
     * @var StringLiteral
     */
    private $userRoleTableName;

    /**
     * @var StringLiteral
     */
    private $rolePermissionTableName;

    public function setUp()
    {
        $this->userRoleTableName = new StringLiteral('user_role');
        $this->rolePermissionTableName = new StringLiteral('role_permission');

        $schemaConfigurator = new SchemaConfigurator(
            $this->userRoleTableName,
            $this->rolePermissionTableName
        );

        $schemaManager = $this->getConnection()->getSchemaManager();

        $schemaConfigurator->configure($schemaManager);

        $this->repository = new UserPermissionsReadRepository(
            $this->getConnection(),
            $this->userRoleTableName,
            $this->rolePermissionTableName
        );
    }

    /**
     * @test
     */
    public function it_should_return_the_permissions_for_a_user_that_are_granted_by_his_roles()
    {
        $userId = new StringLiteral('7D23021B-C9AA-4B64-97A5-ECA8168F4A27');
        $roleId = new StringLiteral('7B6A161E-987B-4069-8BB2-9956B01782CB');

        // Add a role for the user
        $this->getConnection()->insert(
            $this->userRoleTableName,
            array(
                SchemaConfigurator::ROLE_ID_COLUMN => (string) $roleId,
                SchemaConfigurator::USER_ID_COLUMN => (string) $userId
            )
        );

        // Add some permissions to the role we just assigned to the user
        $this->getConnection()->insert(
            $this->rolePermissionTableName,
            array(
                SchemaConfigurator::ROLE_ID_COLUMN => $roleId,
                SchemaConfigurator::PERMISSION_COLUMN => (string) Permission::LABELS_BEHEREN
            )
        );
        $this->getConnection()->insert(
            $this->rolePermissionTableName,
            array(
                SchemaConfigurator::ROLE_ID_COLUMN => $roleId,
                SchemaConfigurator::PERMISSION_COLUMN => (string) Permission::GEBRUIKERS_BEHEREN
            )
        );

        $permissions = $this->repository->getPermissions($userId);

        $expectedPermissions = [
            Permission::LABELS_BEHEREN,
            Permission::GEBRUIKERS_BEHEREN,
        ];

        $this->assertEquals($expectedPermissions, $permissions);
    }
}
