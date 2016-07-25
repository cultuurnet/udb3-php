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
        $expectedOfferLabelRelation = new OfferLabelRelation(
            new UUID(),
            new StringLiteral('purple'),
            OfferType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->dbalWriteRepository->save(
            $expectedOfferLabelRelation->getUuid(),
            $expectedOfferLabelRelation->getLabelName(),
            $expectedOfferLabelRelation->getRelationType(),
            $expectedOfferLabelRelation->getRelationId()
        );

        $actualOfferLabelRelation = $this->getLastOfferLabelRelation();

        $this->assertEquals($expectedOfferLabelRelation, $actualOfferLabelRelation);
    }

    /**
     * @test
     */
    public function it_can_save_same_uuid_but_different_relation_type_and_relation_id()
    {
        $offerLabelRelation = new OfferLabelRelation(
            new UUID(),
            new StringLiteral('blue'),
            OfferType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->saveOfferLabelRelation($offerLabelRelation);

        $expectedOfferLabelRelation = new OfferLabelRelation(
            $offerLabelRelation->getUuid(),
            new StringLiteral('green'),
            OfferType::EVENT(),
            new StringLiteral('otherId')
        );

        $this->dbalWriteRepository->save(
            $expectedOfferLabelRelation->getUuid(),
            $expectedOfferLabelRelation->getLabelName(),
            $expectedOfferLabelRelation->getRelationType(),
            $expectedOfferLabelRelation->getRelationId()
        );

        $actualOfferLabelRelation = $this->getLastOfferLabelRelation();

        $this->assertEquals($expectedOfferLabelRelation, $actualOfferLabelRelation);
    }

    /**
     * @test
     */
    public function it_can_save_same_uuid_and_relation_type_but_different_relation_id()
    {
        $offerLabelRelation = new OfferLabelRelation(
            new UUID(),
            new StringLiteral('blue'),
            OfferType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->saveOfferLabelRelation($offerLabelRelation);

        $expectedOfferLabelRelation = new OfferLabelRelation(
            $offerLabelRelation->getUuid(),
            new StringLiteral('green'),
            $offerLabelRelation->getRelationType(),
            new StringLiteral('otherId')
        );

        $this->dbalWriteRepository->save(
            $expectedOfferLabelRelation->getUuid(),
            $expectedOfferLabelRelation->getLabelName(),
            $expectedOfferLabelRelation->getRelationType(),
            $expectedOfferLabelRelation->getRelationId()
        );

        $actualOfferLabelRelation = $this->getLastOfferLabelRelation();

        $this->assertEquals($expectedOfferLabelRelation, $actualOfferLabelRelation);
    }

    /**
     * @test
     */
    public function it_can_not_save_same_offer_label_relation()
    {
        $offerLabelRelation = new OfferLabelRelation(
            new UUID(),
            new StringLiteral('orange'),
            OfferType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->saveOfferLabelRelation($offerLabelRelation);

        $sameOfferLabelRelation = new OfferLabelRelation(
            $offerLabelRelation->getUuid(),
            $offerLabelRelation->getLabelName(),
            $offerLabelRelation->getRelationType(),
            $offerLabelRelation->getRelationId()
        );

        $this->setExpectedException(UniqueConstraintViolationException::class);

        $this->dbalWriteRepository->save(
            $sameOfferLabelRelation->getUuid(),
            $sameOfferLabelRelation->getLabelName(),
            $sameOfferLabelRelation->getRelationType(),
            $sameOfferLabelRelation->getRelationId()
        );
    }

    /**
     * @test
     */
    public function it_can_delete_based_on_uuid()
    {
        $OfferLabelRelation1 = new OfferLabelRelation(
            new UUID(),
            new StringLiteral('blue'),
            OfferType::PLACE(),
            new StringLiteral('relationId')
        );

        $OfferLabelRelation2 = new OfferLabelRelation(
            new UUID(),
            new StringLiteral('green'),
            OfferType::PLACE(),
            new StringLiteral('otherRelationId')
        );

        $this->saveOfferLabelRelation($OfferLabelRelation1);
        $this->saveOfferLabelRelation($OfferLabelRelation2);

        $this->dbalWriteRepository->deleteByUuidAndRelationId(
            $OfferLabelRelation1->getUuid(),
            $OfferLabelRelation1->getRelationId()
        );

        $this->assertEquals(
            $OfferLabelRelation2->getUuid(),
            $this->getLastOfferLabelRelation()->getUuid()
        );
    }
}
