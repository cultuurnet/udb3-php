<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Doctrine;

use CultuurNet\UDB3\DBALTestConnectionTrait;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;
use ValueObjects\Number\Natural;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\String\String;

class DBALWriteRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use DBALTestConnectionTrait;

    /**
     * @var StringLiteral
     */
    private $tableName;

    /**
     * @var DBALWriteRepository
     */
    private $dbalWriteRepository;

    protected function setUp()
    {
        $this->tableName = new StringLiteral('test_places_json');

        $schemaConfigurator = new SchemaConfigurator($this->tableName);

        $schemaManager = $this->getConnection()->getSchemaManager();

        $schemaConfigurator->configure($schemaManager);

        $this->dbalWriteRepository = new DBALWriteRepository(
            $this->connection,
            $this->tableName
        );
    }

    /**
     * @test
     */
    public function it_can_save()
    {
        $expectedEntity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC(),
            new UUID()
        );

        $this->dbalWriteRepository->save(
            $expectedEntity->getUuid(),
            $expectedEntity->getName(),
            $expectedEntity->getVisibility(),
            $expectedEntity->getPrivacy(),
            $expectedEntity->getParentUuid()
        );

        $actualEntity = $this->getEntity();

        $this->assertEquals($expectedEntity, $actualEntity);
    }

    /**
     * @test
     */
    public function it_can_update_to_visible()
    {
        $entity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::INVISIBLE(),
            Privacy::PRIVACY_PUBLIC(),
            new UUID()
        );

        $this->saveEntity($entity);

        $this->dbalWriteRepository->updateVisible($entity->getUuid());

        $actualEntity = $this->getEntity();

        $this->assertEquals(
            Visibility::VISIBLE(),
            $actualEntity->getVisibility()
        );
    }

    /**
     * @test
     */
    public function it_can_update_to_invisible()
    {
        $entity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC(),
            new UUID()
        );

        $this->saveEntity($entity);

        $this->dbalWriteRepository->updateInvisible($entity->getUuid());

        $actualEntity = $this->getEntity();

        $this->assertEquals(
            Visibility::INVISIBLE(),
            $actualEntity->getVisibility()
        );
    }

    /**
     * @test
     */
    public function it_can_update_to_public()
    {
        $entity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PRIVATE(),
            new UUID()
        );

        $this->saveEntity($entity);

        $this->dbalWriteRepository->updatePrivate($entity->getUuid());

        $actualEntity = $this->getEntity();

        $this->assertEquals(
            Privacy::PRIVACY_PRIVATE(),
            $actualEntity->getPrivacy()
        );
    }

    /**
     * @test
     */
    public function it_can_update_to_private()
    {
        $entity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC(),
            new UUID()
        );

        $this->saveEntity($entity);

        $this->dbalWriteRepository->updatePublic($entity->getUuid());

        $actualEntity = $this->getEntity();

        $this->assertEquals(
            Privacy::PRIVACY_PUBLIC(),
            $actualEntity->getPrivacy()
        );
    }

    /**
     * @test
     */
    public function it_can_increment()
    {
        $expectedEntity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC(),
            new UUID(),
            new Natural(666)
        );

        $this->saveEntity($expectedEntity);

        $this->dbalWriteRepository->updateCountIncrement(
            $expectedEntity->getUuid()
        );

        $actualEntity = $this->getEntity();

        $this->assertEquals(
            new Natural(667),
            $actualEntity->getCount()
        );
    }

    /**
     * @test
     */
    public function it_can_decrement()
    {
        $expectedEntity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC(),
            new UUID(),
            new Natural(666)
        );

        $this->saveEntity($expectedEntity);

        $this->dbalWriteRepository->updateCountDecrement(
            $expectedEntity->getUuid()
        );

        $actualEntity = $this->getEntity();

        $this->assertEquals(
            new Natural(665),
            $actualEntity->getCount()
        );
    }

    /**
     * @param Entity $entity
     */
    private function saveEntity(Entity $entity)
    {
        $values = $this->entityToValues($entity);

        $sql = 'INSERT INTO ' . $this->tableName . ' VALUES (?, ?, ?, ?, ?, ?)';

        $this->connection->executeQuery($sql, $values);
    }

    /**
     * @param Entity $entity
     * @return array
     */
    private function entityToValues(Entity $entity)
    {
        return [
            $entity->getUuid()->toNative(),
            $entity->getName()->toNative(),
            $entity->getVisibility() === Visibility::VISIBLE()
                ? true : false,
            $entity->getPrivacy() === Privacy::PRIVACY_PRIVATE()
                ? true : false,
            $entity->getParentUuid()->toNative(),
            $entity->getCount()->toNative()
        ];
    }

    /**
     * @return Entity
     */
    private function getEntity()
    {
        $sql = 'SELECT * FROM ' . $this->tableName;

        $statement = $this->connection->executeQuery($sql);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return $this->rowToEntity($row);
    }

    /**
     * @param array $row
     * @return Entity
     */
    private function rowToEntity(array $row)
    {
        return new Entity(
            new UUID($row[SchemaConfigurator::UUID_COLUMN]),
            new String($row[SchemaConfigurator::NAME_COLUMN]),
            $row[SchemaConfigurator::VISIBLE_COLUMN]
                ? Visibility::VISIBLE() : Visibility::INVISIBLE(),
            $row[SchemaConfigurator::PRIVATE_COLUMN]
                ? Privacy::PRIVACY_PRIVATE() : Privacy::PRIVACY_PUBLIC(),
            new UUID($row[SchemaConfigurator::PARENT_UUID_COLUMN]),
            new Natural($row[SchemaConfigurator::COUNT_COLUMN])
        );
    }
}
