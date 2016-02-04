<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place\ReadModel\Permission\Doctrine;

use CultuurNet\UDB3\DBALTestConnectionTrait;
use CultuurNet\UDB3\Offer\ReadModel\Permission\Doctrine\DBALRepository;
use CultuurNet\UDB3\Offer\ReadModel\Permission\Doctrine\SchemaConfigurator;
use ValueObjects\String\String;

class DBALRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use DBALTestConnectionTrait;

    /**
     * @var DBALRepository
     */
    private $repository;

    public function setUp()
    {
        $table = new String('place_permission');
        $idField = new String('place_id');

        (new SchemaConfigurator($table, $idField))->configure(
            $this->getConnection()->getSchemaManager()
        );

        $this->repository = new DBALRepository(
            $table,
            $this->getConnection(),
            $idField
        );
    }

    /**
     * @test
     */
    public function it_can_add_and_query_place_permissions()
    {
        $johnDoe = new String('abc');
        $editableByJohnDoe = [
            new String('123'),
            new String('456'),
            new String('789'),
        ];
        $janeDoe = new String('def');
        $editableByJaneDoe = [
            new String('101112'),
            new String('131415'),
            new String('456'),
        ];

        $this->assertEquals(
            [],
            $this->repository->getEditableOffers($johnDoe)
        );

        $this->assertEquals(
            [],
            $this->repository->getEditableOffers($janeDoe)
        );

        array_walk($editableByJohnDoe, [$this, 'markEditable'], $johnDoe);
        array_walk($editableByJaneDoe, [$this, 'markEditable'], $janeDoe);

        $this->assertEquals(
            $editableByJohnDoe,
            $this->repository->getEditableOffers($johnDoe)
        );

        $this->assertEquals(
            $editableByJaneDoe,
            $this->repository->getEditableOffers($janeDoe)
        );
    }

    /**
     * @param String $eventId
     * @param string $key
     * @param String $userId
     */
    private function markEditable(String $placeId, $key, String $userId)
    {
        $this->repository->markOfferEditableByUser($placeId, $userId);
    }

    /**
     * @test
     */
    public function it_silently_ignores_adding_duplicate_permissions()
    {
        $johnDoe = new String('abc');
        $editableByJohnDoe = [
            new String('123'),
            new String('456'),
            new String('789'),
        ];

        array_walk($editableByJohnDoe, [$this, 'markEditable'], $johnDoe);

        $this->repository->markOfferEditableByUser(new String('456'), $johnDoe);

        $this->assertEquals(
            $editableByJohnDoe,
            $this->repository->getEditableOffers($johnDoe)
        );
    }
}
