<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;
use ValueObjects\Number\Natural;
use ValueObjects\String\String as StringLiteral;

class DBALWriteRepositoryTest extends BaseDBALRepositoryTest
{
    /**
     * @var DBALWriteRepository
     */
    private $dbalWriteRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->dbalWriteRepository = new DBALWriteRepository(
            $this->getConnection(),
            $this->getTableName()
        );
    }

    /**
     * @test
     */
    public function it_can_save()
    {
        $expectedEntity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC(),
            new UUID()
        );

        $this->dbalWriteRepository->save(
            $expectedEntity->getUuid(),
            $expectedEntity->getName(),
            $expectedEntity->getVisibility(),
            $expectedEntity->getPrivacy(),
            $expectedEntity->getParentUuid()
        );

        $actualEntity = $this->getEntity();

        $this->assertEquals($expectedEntity, $actualEntity);
    }

    /**
     * @test
     */
    public function it_can_update_to_visible()
    {
        $entity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::INVISIBLE(),
            Privacy::PRIVACY_PUBLIC(),
            new UUID()
        );

        $this->saveEntity($entity);

        $this->dbalWriteRepository->updateVisible($entity->getUuid());

        $actualEntity = $this->getEntity();

        $this->assertEquals(
            Visibility::VISIBLE(),
            $actualEntity->getVisibility()
        );
    }

    /**
     * @test
     */
    public function it_can_update_to_invisible()
    {
        $entity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC(),
            new UUID()
        );

        $this->saveEntity($entity);

        $this->dbalWriteRepository->updateInvisible($entity->getUuid());

        $actualEntity = $this->getEntity();

        $this->assertEquals(
            Visibility::INVISIBLE(),
            $actualEntity->getVisibility()
        );
    }

    /**
     * @test
     */
    public function it_can_update_to_public()
    {
        $entity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PRIVATE(),
            new UUID()
        );

        $this->saveEntity($entity);

        $this->dbalWriteRepository->updatePrivate($entity->getUuid());

        $actualEntity = $this->getEntity();

        $this->assertEquals(
            Privacy::PRIVACY_PRIVATE(),
            $actualEntity->getPrivacy()
        );
    }

    /**
     * @test
     */
    public function it_can_update_to_private()
    {
        $entity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC(),
            new UUID()
        );

        $this->saveEntity($entity);

        $this->dbalWriteRepository->updatePublic($entity->getUuid());

        $actualEntity = $this->getEntity();

        $this->assertEquals(
            Privacy::PRIVACY_PUBLIC(),
            $actualEntity->getPrivacy()
        );
    }

    /**
     * @test
     */
    public function it_can_increment()
    {
        $expectedEntity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC(),
            new UUID(),
            new Natural(666)
        );

        $this->saveEntity($expectedEntity);

        $this->dbalWriteRepository->updateCountIncrement(
            $expectedEntity->getUuid()
        );

        $actualEntity = $this->getEntity();

        $this->assertEquals(
            new Natural(667),
            $actualEntity->getCount()
        );
    }

    /**
     * @test
     */
    public function it_can_decrement()
    {
        $expectedEntity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC(),
            new UUID(),
            new Natural(666)
        );

        $this->saveEntity($expectedEntity);

        $this->dbalWriteRepository->updateCountDecrement(
            $expectedEntity->getUuid()
        );

        $actualEntity = $this->getEntity();

        $this->assertEquals(
            new Natural(665),
            $actualEntity->getCount()
        );
    }
}
