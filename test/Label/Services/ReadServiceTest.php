<?php

namespace CultuurNet\UDB3\Label\Services;

use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Query;
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

    /**
     * @var Query
     */
    private $query;

    protected function setUp()
    {
        $this->entity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::INVISIBLE(),
            Privacy::PRIVACY_PRIVATE()
        );

        $this->query = new Query(new StringLiteral('something'));

        $this->readRepository = $this->getMock(ReadRepositoryInterface::class);
        $this->mockGetByUuid();
        $this->mockSearch();
        $this->mockSearchTotalLabels();

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

    /**
     * @test
     */
    public function it_can_get_label_based_on_query()
    {
        $this->readRepository->expects($this->once())
            ->method('search')
            ->with($this->query);

        $entities = $this->readService->search($this->query);

        $this->assertEquals([$this->entity, $this->entity], $entities);
    }

    /**
     * @test
     */
    public function it_can_get_total_labels_count_based_on_query()
    {
        $this->readRepository->expects($this->once())
            ->method('searchTotalLabels')
            ->with($this->query);

        $totalLabels = $this->readService->searchTotalLabels($this->query);

        $this->assertEquals(10, $totalLabels);
    }

    private function mockGetByUuid()
    {
        $this->readRepository->method('getByUuid')
            ->with($this->entity->getUuid())
            ->willReturn($this->entity);
    }

    private function mockSearch()
    {
        $this->readRepository->method('search')
            ->with($this->query)
            ->willReturn([$this->entity, $this->entity]);
    }

    private function mockSearchTotalLabels()
    {
        $this->readRepository->method('searchTotalLabels')
            ->with($this->query)
            ->willReturn(10);
    }
}
