<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\OfferLabelRelation;
use CultuurNet\UDB3\Offer\OfferType;
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
        $expectedEntity = new OfferLabelRelation(
            new UUID(),
            new StringLiteral('purple'),
            OfferType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->dbalWriteRepository->save(
            $expectedEntity->getUuid(),
            $expectedEntity->getLabelName(),
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
        $entity = new OfferLabelRelation(
            new UUID(),
            new StringLiteral('blue'),
            OfferType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->saveEntity($entity);

        $expectedEntity = new OfferLabelRelation(
            $entity->getUuid(),
            new StringLiteral('green'),
            OfferType::EVENT(),
            new StringLiteral('otherId')
        );

        $this->dbalWriteRepository->save(
            $expectedEntity->getUuid(),
            $expectedEntity->getLabelName(),
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
        $entity = new OfferLabelRelation(
            new UUID(),
            new StringLiteral('blue'),
            OfferType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->saveEntity($entity);

        $expectedEntity = new OfferLabelRelation(
            $entity->getUuid(),
            new StringLiteral('green'),
            $entity->getRelationType(),
            new StringLiteral('otherId')
        );

        $this->dbalWriteRepository->save(
            $expectedEntity->getUuid(),
            $expectedEntity->getLabelName(),
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
        $entity = new OfferLabelRelation(
            new UUID(),
            new StringLiteral('orange'),
            OfferType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->saveEntity($entity);

        $sameEntity = new OfferLabelRelation(
            $entity->getUuid(),
            $entity->getLabelName(),
            $entity->getRelationType(),
            $entity->getRelationId()
        );

        $this->setExpectedException(UniqueConstraintViolationException::class);

        $this->dbalWriteRepository->save(
            $sameEntity->getUuid(),
            $sameEntity->getLabelName(),
            $sameEntity->getRelationType(),
            $sameEntity->getRelationId()
        );
    }

    /**
     * @test
     */
    public function it_can_delete_based_on_uuid()
    {
        $entity1 = new OfferLabelRelation(
            new UUID(),
            new StringLiteral('blue'),
            OfferType::PLACE(),
            new StringLiteral('relationId')
        );

        $entity2 = new OfferLabelRelation(
            new UUID(),
            new StringLiteral('green'),
            OfferType::PLACE(),
            new StringLiteral('otherRelationId')
        );

        $this->saveEntity($entity1);
        $this->saveEntity($entity2);

        $this->dbalWriteRepository->deleteByUuidAndRelationId(
            $entity1->getUuid(),
            $entity1->getRelationId()
        );

        $this->assertEquals(
            $entity2->getUuid(),
            $this->getLastEntity()->getUuid()
        );
    }
}
