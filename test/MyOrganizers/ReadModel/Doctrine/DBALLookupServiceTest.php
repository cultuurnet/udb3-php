<?php

namespace CultuurNet\UDB3\MyOrganizers\ReadModel\Doctrine;

use CultuurNet\UDB3\AbstractDBALTableTest;
use CultuurNet\UDB3\Iri\CallableIriGenerator;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\MyOrganizers\PartOfCollection;
use ValueObjects\Number\Natural;
use ValueObjects\StringLiteral\StringLiteral;

class DBALLookupServiceTest extends AbstractDBALTableTest
{
    /**
     * @var \CultuurNet\UDB3\Iri\IriGeneratorInterface
     */
    private $iriGenerator;

    /**
     * @var \CultuurNet\UDB3\MyOrganizers\ReadModel\Doctrine\DBALLookupService
     */
    private $lookup;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->tableName = new StringLiteral('testtable');

        $schemaManager = $this->getConnection()->getSchemaManager();

        (new SchemaConfigurator($this->tableName))
            ->configure($schemaManager);

        $this->iriGenerator = new CallableIriGenerator(
            function ($id) {
                return 'https://example.com/' . $id;
            }
        );

        $this->lookup = new DBALLookupService(
            $this->getConnection(),
            $this->tableName,
            $this->iriGenerator
        );

        $this->insert(
            $this->loadData(__DIR__ . '/initial-values.json')
        );

    }

    /**
     * @test
     * @dataProvider partsOfCollectionDataProvider
     */
    public function it_lists_most_recently_updated_organizers_owned_by_a_user(
        string $user,
        int $limit,
        int $start,
        PartOfCollection $expected
    ) {
        $partOfCollection = $this->lookup->itemsOwnedByUser(
            $user,
            new Natural($limit),
            new Natural($start)
        );

        $this->assertEquals(
            $expected,
            $partOfCollection
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function partsOfCollectionDataProvider()
    {
        return [
            'user A page 1' => [
                'user' => 'A',
                'limit' => 2,
                'start' => 0,
                'expected' => new PartOfCollection(
                    [
                        [
                            '@id' => 'https://example.com/5',
                            '@type' => 'Organizer',
                        ],
                        [
                            '@id' => 'https://example.com/1',
                            '@type' => 'Organizer',
                        ],
                    ],
                    new Natural(3)
                ),
            ],
            'user A page 2' => [
                'user' => 'A',
                'limit' => 2,
                'start' => 2,
                'expected' => new PartOfCollection(
                    [
                        [
                            '@id' => 'https://example.com/2',
                            '@type' => 'Organizer',
                        ],
                    ],
                    new Natural(3)
                ),
            ],
            'user C all' => [
                'user' => 'C',
                'limit' => 1000,
                'start' => 0,
                'expected' => new PartOfCollection(
                    [
                        [
                            '@id' => 'https://example.com/8',
                            '@type' => 'Organizer',
                        ],
                        [
                            '@id' => 'https://example.com/6',
                            '@type' => 'Organizer',
                        ],
                        [
                            '@id' => 'https://example.com/7',
                            '@type' => 'Organizer',
                        ],
                    ],
                    new Natural(3)
                )
            ]
        ];
    }
}
