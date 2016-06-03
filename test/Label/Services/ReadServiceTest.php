<?php

namespace CultuurNet\UDB3\Label\Services;

use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readRepository;

    /**
     * @var ReadServiceInterface
     */
    private $readService;

    /**
     * @var Entity
     */
    private $entity;

    protected function setUp()
    {
        $this->entity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::INVISIBLE(),
            Privacy::PRIVACY_PRIVATE()
        );

        $this->readRepository = $this->getMock(ReadRepositoryInterface::class);
        $this->mockGetByUuid();

        $this->readService = new ReadService(
            $this->readRepository
        );
    }

    /**
     * @test
     */
    public function it_can_get_label_entity_based_on_uuid()
    {
        $this->readRepository->expects($this->once())
            ->method('getByUuid')
            ->with($this->entity->getUuid());

        $entity = $this->readService->getByUuid($this->entity->getUuid());

        $this->assertEquals($this->entity, $entity);
    }

    private function mockGetByUuid()
    {
        $this->readRepository->method('getByUuid')
            ->with($this->entity->getUuid())
            ->willReturn($this->entity);
    }
}
