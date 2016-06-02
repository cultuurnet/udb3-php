<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Entity;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class DBALWriteRepositoryTest extends BaseDBALRepositoryTest
{
    /**
     * @var DBALWriteRepository
     */
    private $dbalWriteRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->dbalWriteRepository = new DBALWriteRepository(
            $this->getConnection(),
            $this->getTableName()
        );
    }

    /**
     * @test
     */
    public function it_can_save()
    {
        $expectedEntity = new Entity(
            new UUID(),
            RelationType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->dbalWriteRepository->save(
            $expectedEntity->getUuid(),
            $expectedEntity->getRelationType(),
            $expectedEntity->getRelationId()
        );

        $actualEntity = $this->getLastEntity();

        $this->assertEquals($expectedEntity, $actualEntity);
    }

    /**
     * @test
     */
    public function it_can_save_same_uuid_but_different_relation_type_and_relation_id()
    {
        $entity = new Entity(
            new UUID(),
            RelationType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->saveEntity($entity);

        $expectedEntity = new Entity(
            $entity->getUuid(),
            RelationType::EVENT(),
            new StringLiteral('otherId')
        );

        $this->dbalWriteRepository->save(
            $expectedEntity->getUuid(),
            $expectedEntity->getRelationType(),
            $expectedEntity->getRelationId()
        );

        $actualEntity = $this->getLastEntity();

        $this->assertEquals($expectedEntity, $actualEntity);
    }

    /**
     * @test
     */
    public function it_can_save_same_uuid_and_relation_type_but_different_relation_id()
    {
        $entity = new Entity(
            new UUID(),
            RelationType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->saveEntity($entity);

        $expectedEntity = new Entity(
            $entity->getUuid(),
            $entity->getRelationType(),
            new StringLiteral('otherId')
        );

        $this->dbalWriteRepository->save(
            $expectedEntity->getUuid(),
            $expectedEntity->getRelationType(),
            $expectedEntity->getRelationId()
        );

        $actualEntity = $this->getLastEntity();

        $this->assertEquals($expectedEntity, $actualEntity);
    }

    /**
     * @test
     */
    public function it_can_not_save_same_entity()
    {
        $entity = new Entity(
            new UUID(),
            RelationType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->saveEntity($entity);

        $sameEntity = new Entity(
            $entity->getUuid(),
            $entity->getRelationType(),
            $entity->getRelationId()
        );

        $this->setExpectedException(UniqueConstraintViolationException::class);

        $this->dbalWriteRepository->save(
            $sameEntity->getUuid(),
            $sameEntity->getRelationType(),
            $sameEntity->getRelationId()
        );
    }

    /**
     * @test
     */
    public function it_can_delete_based_on_uuid()
    {
        $entity = new Entity(
            new UUID(),
            RelationType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->saveEntity($entity);

        $this->dbalWriteRepository->deleteByUuid($entity->getUuid());

        $this->assertNull($this->getLastEntity());
    }
}
