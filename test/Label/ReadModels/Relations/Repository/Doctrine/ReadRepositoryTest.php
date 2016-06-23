<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Entity;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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
    public function it_should_return_the_ids_of_all_the_offers_that_are_tagged_with_a_specific_label()
    {
        $labelId = new UUID('452ED5F7-925D-4D2C-9FA8-490398E85A16');

        $entity1 = new Entity(
            $labelId,
            RelationType::PLACE(),
            new StringLiteral('99A78F44-A45B-40E2-A1E3-7632D2F3B1C6')
        );

        $entity2 = new Entity(
            $labelId,
            RelationType::PLACE(),
            new StringLiteral('A9B3FA7B-9AF5-49F4-8BB5-2B169CE83107')
        );

        $entity3 = new Entity(
            new UUID(),
            RelationType::PLACE(),
            new StringLiteral('298A39A1-8D1E-4F5D-B05E-811B6459EA36')
        );

        $this->saveEntity($entity1);
        $this->saveEntity($entity2);
        $this->saveEntity($entity3);

        $offerIds = $this->readRepository->getOffersByLabel($labelId);
        $expectedOfferIds = [
            '99A78F44-A45B-40E2-A1E3-7632D2F3B1C6',
            'A9B3FA7B-9AF5-49F4-8BB5-2B169CE83107',
        ];

        $this->assertEquals($expectedOfferIds, $offerIds);
    }
}
