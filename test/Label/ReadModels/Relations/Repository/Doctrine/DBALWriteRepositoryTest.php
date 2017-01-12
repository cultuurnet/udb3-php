<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\LabelRelation;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use ValueObjects\StringLiteral\StringLiteral;

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

        $actualOfferLabelRelation = $this->getLabelRelations();

        $this->assertEquals([$expectedOfferLabelRelation], $actualOfferLabelRelation);
    }

    /**
     * @test
     */
    public function it_can_save_same_label_name_but_different_relation_type_and_relation_id()
    {
        $labelRelation1 = new LabelRelation(
            new LabelName('2dotstwice'),
            RelationType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->saveLabelRelation($labelRelation1);

        $labelRelation2 = new LabelRelation(
            $labelRelation1->getLabelName(),
            RelationType::EVENT(),
            new StringLiteral('otherId')
        );

        $this->dbalWriteRepository->save(
            $labelRelation2->getLabelName(),
            $labelRelation2->getRelationType(),
            $labelRelation2->getRelationId()
        );

        $actualOfferLabelRelation = $this->getLabelRelations();

        $this->assertEquals(
            [
                $labelRelation1,
                $labelRelation2,
            ],
            $actualOfferLabelRelation
        );
    }

    /**
     * @test
     */
    public function it_can_save_same_label_name_and_relation_type_but_different_relation_id()
    {
        $labelRelation1 = new LabelRelation(
            new LabelName('2dotstwice'),
            RelationType::PLACE(),
            new StringLiteral('relationId')
        );

        $this->saveLabelRelation($labelRelation1);

        $labelRelation2 = new LabelRelation(
            $labelRelation1->getLabelName(),
            $labelRelation1->getRelationType(),
            new StringLiteral('otherId')
        );

        $this->dbalWriteRepository->save(
            $labelRelation2->getLabelName(),
            $labelRelation2->getRelationType(),
            $labelRelation2->getRelationId()
        );

        $actualOfferLabelRelation = $this->getLabelRelations();

        $this->assertEquals(
            [
                $labelRelation1,
                $labelRelation2,
            ],
            $actualOfferLabelRelation
        );
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

        $this->saveLabelRelation($offerLabelRelation);

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
    public function it_can_delete_based_on_label_name_and_relation_id()
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

        $this->saveLabelRelation($OfferLabelRelation1);
        $this->saveLabelRelation($OfferLabelRelation2);

        $this->dbalWriteRepository->deleteByLabelNameAndRelationId(
            $OfferLabelRelation1->getLabelName(),
            $OfferLabelRelation1->getRelationId()
        );

        $labelRelations = $this->getLabelRelations();

        $this->assertCount(1, $labelRelations);

        $this->assertEquals(
            $OfferLabelRelation2->getLabelName(),
            $labelRelations[0]->getLabelName()
        );
    }

    /**
     * @test
     */
    public function it_can_delete_based_on_relation_id()
    {
        $LabelRelation1 = new LabelRelation(
            new LabelName('2dotstwice'),
            RelationType::PLACE(),
            new StringLiteral('relationId')
        );

        $labelRelation2 = new LabelRelation(
            new LabelName('cultuurnet'),
            RelationType::PLACE(),
            new StringLiteral('otherRelationId')
        );

        $labelRelation3 = new LabelRelation(
            new LabelName('cultuurnet'),
            RelationType::PLACE(),
            new StringLiteral('relationId')
        );

        $labelRelation4 = new LabelRelation(
            new LabelName('foo'),
            RelationType::PLACE(),
            new StringLiteral('fooId')
        );

        $this->saveLabelRelation($LabelRelation1);
        $this->saveLabelRelation($labelRelation2);
        $this->saveLabelRelation($labelRelation3);
        $this->saveLabelRelation($labelRelation4);

        $this->dbalWriteRepository->deleteByRelationId($LabelRelation1->getRelationId());

        $labelRelations = $this->getLabelRelations();

        $this->assertEquals(
            [
                $labelRelation2,
                $labelRelation4,
            ],
            $labelRelations
        );
    }
}
