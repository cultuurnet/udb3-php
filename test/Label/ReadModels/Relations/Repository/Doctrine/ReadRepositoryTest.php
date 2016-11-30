<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\LabelRelation;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use ValueObjects\String\String as StringLiteral;

class ReadRepositoryTest extends BaseDBALRepositoryTest
{
    /**
     * @var DBALReadRepository
     */
    private $readRepository;

    /**
     * @var LabelName
     */
    private $labelName;

    /**
     * @var LabelRelation
     */
    private $relation1;

    /**
     * @var LabelRelation
     */
    private $relation2;

    protected function setUp()
    {
        parent::setUp();

        $this->readRepository = new DBALReadRepository(
            $this->getConnection(),
            $this->getTableName()
        );

        $this->saveOfferLabelRelations();
    }

    /**
     * @test
     */
    public function it_should_return_relations_of_the_offers_that_are_tagged_with_a_specific_label()
    {
        $offerLabelRelations = [];
        foreach ($this->readRepository->getLabelRelations($this->labelName) as $offerLabelRelation) {
            $offerLabelRelations[] = $offerLabelRelation;
        }

        $expectedRelations = [
            $this->relation1,
            $this->relation2
        ];

        $this->assertEquals($expectedRelations, $offerLabelRelations);
    }

    /**
     * @test
     */
    public function it_returns_empty_array_when_no_relations_found_for_specific_label()
    {
        $offerLabelRelations = [];
        foreach ($this->readRepository->getLabelRelations(new LabelName('missing')) as $offerLabelRelation) {
            $offerLabelRelations[] = $offerLabelRelation;
        }

        $this->assertEmpty($offerLabelRelations);
    }

    private function saveOfferLabelRelations()
    {
        $this->labelName = new LabelName('2dotstwice');

        $this->relation1 = new LabelRelation(
            $this->labelName,
            RelationType::PLACE(),
            new StringLiteral('99A78F44-A45B-40E2-A1E3-7632D2F3B1C6')
        );

        $this->relation2 = new LabelRelation(
            $this->labelName,
            RelationType::PLACE(),
            new StringLiteral('A9B3FA7B-9AF5-49F4-8BB5-2B169CE83107')
        );

        $relation3 = new LabelRelation(
            new LabelName('cultuurnet'),
            RelationType::PLACE(),
            new StringLiteral('298A39A1-8D1E-4F5D-B05E-811B6459EA36')
        );

        $this->saveLabelRelation($this->relation1);
        $this->saveLabelRelation($this->relation2);
        $this->saveLabelRelation($relation3);
    }
}
