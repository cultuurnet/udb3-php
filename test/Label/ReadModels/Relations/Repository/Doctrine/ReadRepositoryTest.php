<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\OfferLabelRelation;
use CultuurNet\UDB3\Offer\OfferType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class ReadRepositoryTest extends BaseDBALRepositoryTest
{
    /**
     * @var ReadRepository
     */
    private $readRepository;

    /**
     * @var UUID
     */
    private $labelId;

    /**
     * @var OfferLabelRelation
     */
    private $relation1;

    /**
     * @var OfferLabelRelation
     */
    private $relation2;

    protected function setUp()
    {
        parent::setUp();

        $this->readRepository = new ReadRepository(
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
        foreach ($this->readRepository->getOfferLabelRelations($this->labelId) as $offerLabelRelation) {
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
        foreach ($this->readRepository->getOfferLabelRelations(new UUID()) as $offerLabelRelation) {
            $offerLabelRelations[] = $offerLabelRelation;
        }

        $this->assertEmpty($offerLabelRelations);
    }

    private function saveOfferLabelRelations()
    {
        $this->labelId = new UUID('452ED5F7-925D-4D2C-9FA8-490398E85A16');

        $this->relation1 = new OfferLabelRelation(
            $this->labelId,
            new StringLiteral('green'),
            OfferType::PLACE(),
            new StringLiteral('99A78F44-A45B-40E2-A1E3-7632D2F3B1C6')
        );

        $this->relation2 = new OfferLabelRelation(
            $this->labelId,
            new StringLiteral('green'),
            OfferType::PLACE(),
            new StringLiteral('A9B3FA7B-9AF5-49F4-8BB5-2B169CE83107')
        );

        $relation3 = new OfferLabelRelation(
            new UUID(),
            new StringLiteral('blue'),
            OfferType::PLACE(),
            new StringLiteral('298A39A1-8D1E-4F5D-B05E-811B6459EA36')
        );

        $this->saveOfferLabelRelation($this->relation1);
        $this->saveOfferLabelRelation($this->relation2);
        $this->saveOfferLabelRelation($relation3);
    }
}
