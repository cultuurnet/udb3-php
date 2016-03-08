<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\ReadModel\Search\Doctrine;

use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use CultuurNet\UDB3\DBALTestConnectionTrait;
use CultuurNet\UDB3\Variations\Model\OfferVariation;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use CultuurNet\UDB3\Variations\ReadModel\Search\Criteria;
use PHPUnit_Framework_TestCase;

class DBALRepositoryTest extends PHPUnit_Framework_TestCase
{
    use DBALTestConnectionTrait;

    /**
     * @var DBALRepository
     */
    private $repository;

    /**
     * @var EventVariation[]
     */
    private $variations;

    /**
     * @var Purpose[]
     */
    private $purposes;

    /**
     * @var OwnerId[]
     */
    private $owners;

    /**
     * @var Url[]
     */
    private $urls;

    public function setUp()
    {
        $this->repository = new DBALRepository(
            $this->getConnection(),
            new ExpressionFactory()
        );

        $schemaManager = $this->getConnection()->getSchemaManager();
        $schema = $schemaManager->createSchema();

        $schemaManager->createTable(
            $this->repository->configureSchema($schema)
        );

        $this->generateTestData();
    }

    private function generateTestData()
    {
        $uuidGenerator = new Version4Generator();

        foreach (range(0, 9) as $i) {
            $this->owners[$i] = new OwnerId($uuidGenerator->generate());
            $this->purposes[$i] = new Purpose('purpose ' . $i);
            $this->urls[$i] = new Url(
                '//io.uitdatabank.be/event/' . $uuidGenerator->generate()
            );
        }

        $this->variations = [];

        foreach ($this->owners as $owner) {
            foreach ($this->purposes as $purpose) {
                foreach ($this->urls as $url) {
                    $id = new Id($uuidGenerator->generate());

                    $this->variations[] = OfferVariation::create(
                        $id,
                        $url,
                        $owner,
                        $purpose,
                        new Description('description of variation ' . $id)
                    );

                    $this->repository->save(
                        $id,
                        $url,
                        $owner,
                        $purpose
                    );
                }
            }
        }
    }

    private function getVariationsMatching(Criteria $specification)
    {
        $matches = [];
        foreach ($this->variations as $variation) {
            if ($specification->isSatisfiedBy($variation)) {
                $matches[] = $variation->getAggregateRootId();
            }
        }
        return $matches;
    }

    /**
     * @return array
     */
    public function keysProvider()
    {
        return [
            [0, 5, 9],
            [5, 0, 9],
            [9, 5, 0],
            [null, 0, 0],
            [0, null, 0],
            [0, 0, null],
            [null, null, 0],
            [0, null, null],
            [null, 0, null],
            [null, null, null],
        ];
    }

    /**
     * @test
     * @param integer|null $ownerKey
     * @param integer|null $purposeKey
     * @param integer|null $urlKey
     *
     * @dataProvider keysProvider
     */
    public function it_allows_to_search_for_event_variations_with_criteria(
        $ownerKey,
        $purposeKey,
        $urlKey
    ) {
        $criteria = $this->buildCriteria($ownerKey, $purposeKey, $urlKey);

        $limit = 5;
        $page = 0;

        $this->assertEquals(
            array_slice($this->getVariationsMatching($criteria), $page * $limit, $limit),
            $this->repository->getOfferVariations($criteria, $limit, $page)
        );
    }

    /**
     * @param integer|null $ownerKey
     * @param integer|null $purposeKey
     * @param integer|null $urlKey
     * @return Criteria
     */
    private function buildCriteria($ownerKey, $purposeKey, $urlKey)
    {
        $criteria = new Criteria();
        if (null !== $ownerKey) {
            $criteria = $criteria->withOwnerId($this->owners[$ownerKey]);
        }

        if (null !== $purposeKey) {
            $criteria = $criteria->withPurpose($this->purposes[$purposeKey]);
        }

        if (null !== $urlKey) {
            $criteria = $criteria->withEventUrl($this->urls[$urlKey]);
        }

        return $criteria;
    }

    /**
     * @test
     *
     * @param int|null $ownerKey
     * @param int|null $purposeKey
     * @param int|null $urlKey
     *
     * @dataProvider keysProvider
     */
    public function it_can_count_event_variations_with_criteria(
        $ownerKey,
        $purposeKey,
        $urlKey
    ) {
        $criteria = $this->buildCriteria($ownerKey, $purposeKey, $urlKey);

        $this->assertEquals(
            count($this->getVariationsMatching($criteria)),
            $this->repository->countOfferVariations($criteria)
        );
    }

    /**
     * @test
     */
    public function it_can_remove_a_variation_from_the_search_index()
    {
        $criteria = (new Criteria())
            ->withOwnerId($this->owners[1])
            ->withPurpose($this->purposes[2]);

        $count = $this->repository->countOfferVariations($criteria);
        $this->assertEquals(10, $count);

        $variationIds = $this->repository->getOfferVariations($criteria);

        $firstVariationId = array_shift($variationIds);
        $lastVariationId = array_pop($variationIds);

        $this->repository->remove(new Id($firstVariationId));
        $this->repository->remove(new Id($lastVariationId));

        $this->assertEquals(
            $variationIds,
            $this->repository->getOfferVariations($criteria, $count)
        );

        $this->assertEquals(
            $count - 2,
            $this->repository->countOfferVariations($criteria)
        );
    }
}
