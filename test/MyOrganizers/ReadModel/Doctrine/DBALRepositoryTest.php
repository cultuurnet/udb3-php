<?php

namespace CultuurNet\UDB3\MyOrganizers\ReadModel\Doctrine;

use CultuurNet\UDB3\AbstractDBALTableTest;
use DateTime;
use DateTimeZone;
use ValueObjects\StringLiteral\StringLiteral;

class DBALRepositoryTest extends AbstractDBALTableTest
{
    /**
     * @var DBALRepository
     */
    private $repository;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->tableName = new StringLiteral('testtable');

        $this->repository = new DBALRepository(
            $this->getConnection(),
            $this->tableName
        );

        $schemaManager = $this->getConnection()->getSchemaManager();

        (new SchemaConfigurator($this->tableName))
            ->configure($schemaManager);
    }

    /**
     * @test
     */
    public function it_stores_organizer_ownership_data()
    {
        $createdDate = new \DateTime(
            '2014-06-30T11:49:28',
            new \DateTimeZone('Europe/Brussels')
        );

        $this->repository->add(
            'organizer-id',
            'user-id',
            $createdDate
        );

        $created = $updated = $createdDate->getTimestamp();

        $expectedData = [
            (object)[
                'id' => 'organizer-id',
                'uid' => 'user-id',
                'created' => $created,
                'updated' => $updated,
            ],
        ];

        $this->assertCurrentData($expectedData);
    }

    /**
     * @test
     */
    public function it_updates_organizer_updated_date()
    {
        $created = new DateTime(
            '2014-06-30T11:49:28',
            new DateTimeZone('Europe/Brussels')
        );

        $this->repository->add(
            'organizer-id',
            'user-id',
            $created
        );

        $updated = new \DateTime(
            '2014-07-01T10:01:15',
            new \DateTimeZone('Europe/Brussels')
        );

        $this->repository->setUpdateDate(
            'organizer-id',
            $updated
        );

        $expectedData = [
            (object)[
                'id' => 'organizer-id',
                'uid' => 'user-id',
                'created' => $created->getTimestamp(),
                'updated' => $updated->getTimestamp(),
            ],
        ];

        $this->assertCurrentData($expectedData);
    }

    /**
     * @test
     */
    public function it_deletes_organizer_ownership_data()
    {
        $this->repository->add(
            'organizer-id',
            'user-id',
            new DateTime(
                '2014-06-30T11:49:28',
                new DateTimeZone('Europe/Brussels')
            )
        );

        $this->repository->delete('organizer-id');

        $this->assertCurrentData([]);
    }
}
