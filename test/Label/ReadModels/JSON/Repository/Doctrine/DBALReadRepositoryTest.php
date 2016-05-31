<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;
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
}
