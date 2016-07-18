<?php

namespace CultuurNet\UDB3\Role\ReadModel\Search\Doctrine;

use CultuurNet\UDB3\DBALTestConnectionTrait;
use PHPUnit_Framework_TestCase;
use ValueObjects\String\String as StringLiteral;

class DBALRepositoryTest extends PHPUnit_Framework_TestCase
{
    use DBALTestConnectionTrait;

    /**
     * @var DBALRepository
     */
    private $dbalRepository;

    /**
     * @var array
     */
    private $role;

    /**
     * @var StringLiteral
     */
    private $tableName;

    protected function setUp()
    {
        $this->tableName = new StringLiteral('test_roles_search');

        $schemaConfigurator = new SchemaConfigurator($this->tableName);
        $schemaManager = $this->getConnection()->getSchemaManager();
        $schemaConfigurator->configure($schemaManager);

        $this->dbalRepository = new DBALRepository(
            $this->getConnection(),
            $this->getTableName()
        );

        $this->role = array('uuid' => 'role_uuid', 'name' => 'role_name');
    }

    /**
     * @test
     */
    public function it_can_save()
    {
        $expectedRole = $this->role;

        $this->dbalRepository->save(
            $expectedRole['uuid'],
            $expectedRole['name']
        );

        $actualRole = $this->getLastRole();

        $this->assertEquals($expectedRole, $actualRole);
    }

    /**
     * @test
     */
    public function it_can_update()
    {
        $expectedRole = $this->role;

        $this->dbalRepository->save(
            $expectedRole['uuid'],
            $expectedRole['name']
        );

        $expectedRole['name'] = 'new_role_name';

        $this->dbalRepository->update($expectedRole['uuid'], $expectedRole['name']);

        $actualRole = $this->getLastRole();

        $this->assertEquals($expectedRole, $actualRole);
    }

    /**
     * @test
     */
    public function it_can_remove()
    {
        $expectedRole = $this->role;

        $this->dbalRepository->save(
            $expectedRole['uuid'],
            $expectedRole['name']
        );

        $this->dbalRepository->remove($expectedRole['uuid']);

        $this->assertNull($this->getLastRole());
    }

    /**
     * @test
     */
    public function it_can_search()
    {
        $expectedRole1 = $this->role;
        $expectedRole2 = array(
            'uuid' => 'role_uuid_1',
            'name' => 'role_name with foo in it',
        );
        $expectedRole3 = array(
            'uuid' => 'role_uuid_2',
            'name' => 'role_name with bar in it',
        );

        $expectedRoles = array(
            $expectedRole1,
            $expectedRole2,
            $expectedRole3,
        );

        foreach ($expectedRoles as $role) {
            $this->dbalRepository->save(
                $role['uuid'],
                $role['name']
            );
        }

        // Search everything, results are sorted alphabetically.
        $actualResults = $this->dbalRepository->search();

        $this->assertEquals(
            array(
                $expectedRole1,
                $expectedRole3,
                $expectedRole2,
            ),
            $actualResults->getMember()
        );

        $this->assertEquals(
            10,
            $actualResults->getItemsPerPage()
        );

        // Search everything, results are sorted alphabetically.
        $actualResults = $this->dbalRepository->search('with', 5);

        $this->assertEquals(
            array(
                $expectedRole3,
                $expectedRole2,
            ),
            $actualResults->getMember()
        );

        $this->assertEquals(
            5,
            $actualResults->getItemsPerPage()
        );
    }

    /**
     * @return array
     */
    protected function getLastRole()
    {
        $sql = 'SELECT * FROM ' . $this->tableName;

        $statement = $this->connection->executeQuery($sql);
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $rows ? $rows[count($rows) - 1] : null;
    }

    /**
     * @return StringLiteral
     */
    protected function getTableName()
    {
        return $this->tableName;
    }
}
