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

    protected function setUp()
    {
        parent::setUp();

        $this->readRepository = new ReadRepository(
            $this->getConnection(),
            $this->getTableName()
        );
    }

    /**
     * @test
     */
    public function it_should_return_relations_of_the_offers_that_are_tagged_with_a_specific_label()
    {
        $labelId = new UUID('452ED5F7-925D-4D2C-9FA8-490398E85A16');

        $relation1 = new OfferLabelRelation(
            $labelId,
            new StringLiteral('green'),
            OfferType::PLACE(),
            new StringLiteral('99A78F44-A45B-40E2-A1E3-7632D2F3B1C6')
        );

        $relation2 = new OfferLabelRelation(
            $labelId,
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

        $this->saveEntity($relation1);
        $this->saveEntity($relation2);
        $this->saveEntity($relation3);

        $offerIds = $this->readRepository->getOfferLabelRelations($labelId);
        $expectedRelations = [
            $relation1,
            $relation2
        ];

        $this->assertEquals($expectedRelations, $offerIds);
    }
}
