<?php

namespace CultuurNet\UDB3\Role\ReadModel\Constraints\Doctrine;

use CultuurNet\UDB3\DBALTestConnectionTrait;
use CultuurNet\UDB3\Role\ReadModel\Constraints\UserConstraintsWriteRepositoryInterface;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class UserConstraintWriteRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use DBALTestConnectionTrait;

    /**
     * @var StringLiteral
     */
    private $roleConstraintTableName;

    /**
     * @var UserConstraintsWriteRepositoryInterface
     */
    private $userConstraintsWriteRepository;

    protected function setUp()
    {
        $this->roleConstraintTableName = new StringLiteral('role_constraint');

        $schemaConfigurator = new SchemaConfigurator($this->roleConstraintTableName);
        $schemaConfigurator->configure($this->getConnection()->getSchemaManager());

        $this->userConstraintsWriteRepository = new UserConstraintsWriteRepository(
            $this->connection,
            $this->roleConstraintTableName
        );
    }

    /**
     * @test
     */
    public function it_can_insert_a_role_with_constraint()
    {
        $roleId = new UUID();
        $constraint = new StringLiteral('zipCode:3000');

        $this->userConstraintsWriteRepository->insertRole($roleId, $constraint);

        $expectedRows = [[
            SchemaConfigurator::ROLE_ID_COLUMN => $roleId->toNative(),
            SchemaConfigurator::CONSTRAINT_COLUMN => $constraint->toNative()
        ]];
        $actualRows = $this->getTableRows($this->roleConstraintTableName);

        $this->assertEquals($expectedRows, $actualRows);
    }

    /**
     * @test
     */
    public function it_can_update_a_role_with_constraint()
    {
        $roleId = new UUID();
        $constraint = new StringLiteral('zipCode:3000');

        $this->getConnection()->insert(
            $this->roleConstraintTableName,
            [
                SchemaConfigurator::ROLE_ID_COLUMN => $roleId->toNative(),
                SchemaConfigurator::CONSTRAINT_COLUMN => 'zipCode:1000'
            ]
        );

        $this->userConstraintsWriteRepository->updateRole($roleId, $constraint);

        $expectedRows = [[
            SchemaConfigurator::ROLE_ID_COLUMN => $roleId->toNative(),
            SchemaConfigurator::CONSTRAINT_COLUMN => $constraint->toNative()
        ]];
        $actualRows = $this->getTableRows($this->roleConstraintTableName);

        $this->assertEquals($expectedRows, $actualRows);
    }

    /**
     * @test
     */
    public function it_can_delete_a_role()
    {
        $roleId = new UUID();
        $constraint = new StringLiteral('zipCode:3000');

        $this->getConnection()->insert(
            $this->roleConstraintTableName,
            [
                SchemaConfigurator::ROLE_ID_COLUMN => $roleId->toNative(),
                SchemaConfigurator::CONSTRAINT_COLUMN => $constraint->toNative()
            ]
        );

        $this->userConstraintsWriteRepository->removeRole($roleId);

        $expectedRows = [];
        $actualRows = $this->getTableRows($this->roleConstraintTableName);

        $this->assertEquals($expectedRows, $actualRows);
    }

    /**
     * @param $tableName
     * @return array
     */
    private function getTableRows($tableName)
    {
        $sql = 'SELECT * FROM ' . $tableName;

        $statement = $this->getConnection()->executeQuery($sql);
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $rows;
    }
}
