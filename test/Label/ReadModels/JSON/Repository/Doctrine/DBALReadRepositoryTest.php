<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Query;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;
use ValueObjects\Number\Natural;
use ValueObjects\String\String as StringLiteral;

class DBALReadRepositoryTest extends BaseDBALRepositoryTest
{
    /**
     * @var DBALReadRepository
     */
    private $dbalReadRepository;

    /**
     * @var Entity
     */
    private $entityByUuid;

    /**
     * @var Entity
     */
    private $entityByName;

    protected function setUp()
    {
        parent::setUp();

        $this->dbalReadRepository = new DBALReadRepository(
            $this->getConnection(),
            $this->getTableName()
        );

        $this->entityByUuid = new Entity(
            new Uuid(),
            new StringLiteral('byUuid'),
            Visibility::INVISIBLE(),
            Privacy::PRIVACY_PRIVATE(),
            new Uuid()
        );
        $this->saveEntity($this->entityByUuid);

        $this->entityByName = new Entity(
            new Uuid(),
            new StringLiteral('byName'),
            Visibility::INVISIBLE(),
            Privacy::PRIVACY_PRIVATE(),
            new Uuid()
        );
        $this->saveEntity($this->entityByName);

        for ($i = 0; $i < 10; $i++) {
            $entity = new Entity(
                new Uuid(),
                new StringLiteral('label' . $i),
                Visibility::VISIBLE(),
                Privacy::PRIVACY_PUBLIC(),
                new Uuid()
            );
            $this->saveEntity($entity);
        }
    }

    /**
     * @test
     */
    public function it_can_get_by_uuid()
    {
        $entity = $this->dbalReadRepository->getByUuid(
            $this->entityByUuid->getUuid()
        );

        $this->assertEquals($this->entityByUuid, $entity);
    }

    /**
     * @test
     */
    public function it_returns_null_when_not_found_by_uuid()
    {
        $entity = $this->dbalReadRepository->getByUuid(
            new UUID()
        );

        $this->assertNull($entity);
    }

    /**
     * @test
     */
    public function it_can_get_by_name()
    {
        $entity = $this->dbalReadRepository->getByName(
            $this->entityByName->getName()
        );

        $this->assertEquals($this->entityByName, $entity);
    }

    /**
     * @test
     */
    public function it_returns_null_when_not_found_by_name()
    {
        $entity = $this->dbalReadRepository->getByName(
            new StringLiteral('notFoundName')
        );

        $this->assertNull($entity);
    }

    /**
     * @test
     */
    public function it_can_search_on_exact_name()
    {
        $search = new Query(new StringLiteral('label1'));

        $entities = $this->dbalReadRepository->search($search);

        $this->assertEquals(1, count($entities));
    }

    /**
     * @test
     */
    public function it_can_search_on_name_part()
    {
        $search = new Query(new StringLiteral('labe'));

        $entities = $this->dbalReadRepository->search($search);

        $this->assertEquals(10, count($entities));
    }

    /**
     * @test
     */
    public function it_can_search_on_name_case_insensitive()
    {
        $search = new Query(new StringLiteral('LAB'));

        $entities = $this->dbalReadRepository->search($search);

        $this->assertEquals(10, count($entities));
    }

    /**
     * @test
     */
    public function it_can_search_with_offset()
    {
        $search = new Query(
            new StringLiteral('label'),
            new Natural(5)
        );

        $entities = $this->dbalReadRepository->search($search);

        $this->assertEquals(5, count($entities));
        $this->assertEquals('label5', $entities[0]->getName()->toNative());
        $this->assertEquals('label9', $entities[4]->getName()->toNative());
    }

    /**
     * @test
     */
    public function it_can_search_with_offset_and_limit()
    {
        $search = new Query(
            new StringLiteral('label'),
            new Natural(4),
            new Natural(3)
        );

        $entities = $this->dbalReadRepository->search($search);

        $this->assertEquals(3, count($entities));
        $this->assertEquals('label4', $entities[0]->getName()->toNative());
        $this->assertEquals('label6', $entities[2]->getName()->toNative());
    }

    /**
     * @test
     */
    public function it_can_search_with_limit()
    {
        $search = new Query(
            new StringLiteral('label'),
            null,
            new Natural(3)
        );

        $entities = $this->dbalReadRepository->search($search);

        $this->assertEquals(3, count($entities));
        $this->assertEquals('label0', $entities[0]->getName()->toNative());
        $this->assertEquals('label2', $entities[2]->getName()->toNative());
    }

    /**
     * @test
     */
    public function it_returns_null_when_nothing_matches_search()
    {
        $search = new Query(new StringLiteral('nothing_please'));

        $entities = $this->dbalReadRepository->search($search);

        $this->assertNull($entities);
    }

    /**
     * @test
     */
    public function it_can_get_total_items_of_search()
    {
        $search = new Query(new StringLiteral('lab'));

        $totalLabels = $this->dbalReadRepository->searchTotalLabels($search);

        $this->assertEquals(new Natural(10), $totalLabels);
    }

    /**
     * @test
     */
    public function it_returns_zero_for_total_items_when_search_did_match_nothing()
    {
        $search = new Query(new StringLiteral('nothing'));

        $totalLabels = $this->dbalReadRepository->searchTotalLabels($search);

        $this->assertEquals(new Natural(0), $totalLabels);
    }
}
