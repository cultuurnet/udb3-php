<?php

namespace CultuurNet\UDB3\ReadModel\Index\Doctrine;

use CultuurNet\UDB3\AbstractDBALTableTest;
use CultuurNet\UDB3\DBALTestConnectionTrait;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\ReadModel\Index\EntityIriGeneratorFactoryInterface;
use CultuurNet\UDB3\ReadModel\Index\EntityType;
use PDO;
use PHPUnit\Framework\MockObject\MockObject;
use ValueObjects\Number\Integer;
use ValueObjects\Number\Natural;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Domain;
use ValueObjects\Web\Url;

class DBALRepositoryTest extends AbstractDBALTableTest
{
    /**
     * @var DBALRepository
     */
    protected $repository;

    /**
     * @var EntityIriGeneratorFactoryInterface|MockObject
     */
    protected $iriGeneratorFactory;

    /**
     * @var IriGeneratorInterface|MockObject
     */
    protected $iriGenerator;

    /**
     * @var array
     */
    protected $data;

    public function setUp()
    {
        $this->tableName = new StringLiteral('testtable');

        $schemaManager = $this->getConnection()->getSchemaManager();

        (new SchemaConfigurator($this->tableName))
            ->configure($schemaManager);

        $this->data = $this->loadData(__DIR__ . '/initial-values.json');

        $this->insert($this->data);

        $this->iriGeneratorFactory = $this->createMock(EntityIriGeneratorFactoryInterface::class);
        $this->iriGenerator = $this->createMock(IriGeneratorInterface::class);

        $this->iriGeneratorFactory
            ->method('forEntityType')
            ->willReturn($this->iriGenerator);

        $this->repository = new DBALRepository(
            $this->getConnection(),
            $this->tableName,
            $this->iriGeneratorFactory
        );
    }

    /**
     * @test
     */
    public function it_updates_existing_data_by_unique_combination_of_id_and_entity_type()
    {
        $this->iriGenerator
            ->method('iri')
            ->willReturn('http://hello.world/something/abc');

        $this->repository->updateIndex(
            'abc',
            EntityType::EVENT(),
            'bar',
            'Test event abc update',
            '3020',
            'Herent',
            'BE',
            Domain::specifyType('udb.be'),
            new \DateTimeImmutable('@100')
        );

        $expectedData = $this->data;

        $expectedData[3] = [
            'uid' => 'bar',
            'title' => 'Test event abc update',
            'created' => '100',
            'zip' => '3020',
            'city' => 'Herent',
            'country' => 'BE',
        ] + (array) $expectedData[3];

        $expectedData[3] = (object) $expectedData[3];

        $this->assertCurrentData($expectedData);
    }

    /**
     * @test
     */
    public function it_updates_owning_domain_and_entity_iri()
    {
        $this->iriGenerator
            ->method('iri')
            ->willReturn('http://hello.world/something/blub');

        $this->repository->updateIndex(
            'blub',
            EntityType::EVENT(),
            'bar',
            'Test event abc update',
            '3020',
            'Herent',
            'BE',
            Domain::specifyType('udb.be'),
            new \DateTimeImmutable('@100')
        );

        $expectedData = $this->data;

        $expectedData[5] = [
                'uid' => 'bar',
                'title' => 'Test event abc update',
                'created' => '100',
                'zip' => '3020',
                'city' => 'Herent',
                'country' => 'BE',
                'owning_domain' => 'udb.be',
                'entity_iri' => 'http://hello.world/something/blub',
            ] + (array) $expectedData[5];

        $expectedData[5] = (object) $expectedData[5];

        $this->assertCurrentData($expectedData);
    }

    /**
     * @test
     */
    public function it_inserts_new_unique_combinations_of_id_and_entity_type()
    {
        $this->iriGenerator
            ->method('iri')
            ->willReturn('http://hello.world/something/id');

        $this->repository->updateIndex(
            'xyz',
            EntityType::EVENT(),
            'foo',
            'Test event xyz',
            '3020',
            'Herent',
            'BE',
            Domain::specifyType('udb.be'),
            new \DateTimeImmutable('@0')
        );

        $expectedData = $this->data;

        $expectedData[] = (object)[
            'entity_id' => 'xyz',
            'entity_type' => 'event',
            'uid' => 'foo',
            'title' => 'Test event xyz',
            'zip' => '3020',
            'city' => 'Herent',
            'country' => 'BE',
            'created' => 0,
            'updated' => 0,
            'owning_domain' => 'udb.be',
            'entity_iri' => 'http://hello.world/something/id',
        ];

        $this->assertCurrentData($expectedData);
    }

    /**
     * @test
     */
    public function it_deletes_by_unique_combination_of_id_and_entity_type()
    {
        $this->repository->deleteIndex('abcd', EntityType::PLACE());

        $expectedData = $this->data;

        unset($expectedData[0]);

        $this->assertCurrentData($expectedData);

        $this->repository->deleteIndex('abc', EntityType::EVENT());

        unset($expectedData[3]);

        $this->assertCurrentData($expectedData);
    }

    /**
     * @test
     */
    public function it_can_find_places_by_postal_code()
    {
        $expectedIds = [
            'abcd',
            '123',
        ];

        $this->assertEquals(
            $expectedIds,
            $this->repository->findPlacesByPostalCode('3000', 'BE')
        );
    }

    /**
     * @test
     */
    public function it_can_find_places_by_city_name()
    {
        $expectedIds = [
            'abcd',
            '123',
        ];

        $this->assertEquals(
            $expectedIds,
            $this->repository->findPlacesByCity('Leuven', 'BE')
        );
    }

    /**
     * @test
     */
    public function it_should_update_the_updated_column_when_setting_the_updated_date()
    {
        $itemId = 'def';
        $dateUpdated = new \DateTime();
        $dateUpdated->setTimestamp(1171502725);

        $expectedData = $this->data;

        $expectedData[1] = [
                'updated' => 1171502725,
            ] + (array) $expectedData[1];

        $expectedData[1] = (object) $expectedData[1];

        $this->repository->setUpdateDate($itemId, $dateUpdated);

        $this->assertCurrentData($expectedData);
    }

    /**
     * @test
     */
    public function it_should_return_a_list_of_paged_items_when_looking_for_user_dashboard_content()
    {
        $limit = Natural::fromNative(5);
        $start = Natural::fromNative(0);
        $userId = 'bar';

        $pagedCollection = $this->repository->findByUser($userId, $limit, $start);

        $expectedItems = [
            new IriOfferIdentifier(Url::fromNative('http://hello.world/something/123'), '123', OfferType::PLACE()),
        ];

        $this->assertEquals($expectedItems, $pagedCollection->getItems());
        $this->assertEquals(Integer::fromNative(1), $pagedCollection->getTotalItems());
    }

    /**
     * @test
     */
    public function it_should_return_a_list_of_paged_items_when_looking_for_user_dashboard_content_for_a_specific_domain()
    {
        $limit = Natural::fromNative(5);
        $start = Natural::fromNative(0);
        $userId = 'foo';
        $domain = Domain::specifyType('omd.be');

        $pagedCollection = $this->repository->findByUserForDomain(
            $userId,
            $limit,
            $start,
            $domain
        );

        $expectedItems = [
            new IriOfferIdentifier(Url::fromNative('http://hello.world/something/ghj'), 'ghj', OfferType::EVENT()),
        ];

        $this->assertEquals($expectedItems, $pagedCollection->getItems());
        $this->assertEquals(Integer::fromNative(1), $pagedCollection->getTotalItems());
    }

    /**
     * @test
     */
    public function it_should_return_the_total_items_when_there_are_multiple_pages_of_dashboard_items()
    {
        $userId = 'foo';
        $limit = Natural::fromNative(2);
        $start = Natural::fromNative(0);

        $pagedCollection = $this->repository->findByUser($userId, $limit, $start);

        $this->assertEquals(Integer::fromNative(5), $pagedCollection->getTotalItems());
    }
}
