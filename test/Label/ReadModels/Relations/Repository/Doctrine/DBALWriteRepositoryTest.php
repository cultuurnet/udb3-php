<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\LabelRelation;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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
        $expectedOfferLabelRelation = new LabelRelation(
            new LabelName('2dotstwice'),
            RelationType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->dbalWriteRepository->save(
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
        $offerLabelRelation = new LabelRelation(
            new LabelName('2dotstwice'),
            RelationType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->saveOfferLabelRelation($offerLabelRelation);

        $expectedOfferLabelRelation = new LabelRelation(
            $offerLabelRelation->getLabelName(),
            RelationType::EVENT(),
            new StringLiteral('otherId')
        );

        $this->dbalWriteRepository->save(
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
        $offerLabelRelation = new LabelRelation(
            new LabelName('2dotstwice'),
            RelationType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->saveOfferLabelRelation($offerLabelRelation);

        $expectedOfferLabelRelation = new LabelRelation(
            $offerLabelRelation->getLabelName(),
            $offerLabelRelation->getRelationType(),
            new StringLiteral('otherId')
        );

        $this->dbalWriteRepository->save(
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
        $offerLabelRelation = new LabelRelation(
            new LabelName('2dotstwice'),
            RelationType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->saveOfferLabelRelation($offerLabelRelation);

        $sameOfferLabelRelation = new LabelRelation(
            $offerLabelRelation->getLabelName(),
            $offerLabelRelation->getRelationType(),
            $offerLabelRelation->getRelationId()
        );

        $this->setExpectedException(UniqueConstraintViolationException::class);

        $this->dbalWriteRepository->save(
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
        $OfferLabelRelation1 = new LabelRelation(
            new LabelName('2dotstwice'),
            RelationType::PLACE(),
            new StringLiteral('relationId')
        );

        $OfferLabelRelation2 = new LabelRelation(
            new LabelName('cultuurnet'),
            RelationType::PLACE(),
            new StringLiteral('otherRelationId')
        );

        $this->saveOfferLabelRelation($OfferLabelRelation1);
        $this->saveOfferLabelRelation($OfferLabelRelation2);

        $this->dbalWriteRepository->deleteByLabelNameAndRelationId(
            $OfferLabelRelation1->getLabelName(),
            $OfferLabelRelation1->getRelationId()
        );

        $this->assertEquals(
            $OfferLabelRelation2->getLabelName(),
            $this->getLastOfferLabelRelation()->getLabelName()
        );
    }
}
