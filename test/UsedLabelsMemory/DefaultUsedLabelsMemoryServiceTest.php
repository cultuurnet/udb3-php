<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedLabelsMemory;

use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Label;

class DefaultUsedLabelsMemoryServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultUsedLabelsMemoryService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RepositoryInterface
     */
    protected $repository;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->repository = $this->getMock(RepositoryInterface::class);
        $this->service = new DefaultUsedLabelsMemoryService(
            $this->repository
        );
    }

    /**
     * @test
     */
    public function it_remembers_labels_per_user_in_a_repository()
    {
        $userId = 1;
        $label = new Label('classical rock');

        $usedLabelsMemory = $this->getMock(UsedLabelsMemory::class);

        $this->repository->expects($this->once())
            ->method('load')
            ->with($userId)
            ->will($this->returnValue($usedLabelsMemory));

        $usedLabelsMemory->expects($this->once())
            ->method('labelUsed')
            ->with($label);

        $this->repository->expects(($this->once()))
            ->method('add')
            ->with($usedLabelsMemory);

        $this->service->rememberLabelUsed($userId, $label);
    }

    /**
     * @test
     */
    public function it_initiates_an_empty_memory_for_new_users()
    {
        $userId = 2;
        $label = new Label('jazz');

        $this->repository->expects($this->once())
            ->method('load')
            ->with($userId)
            ->will(
                $this->throwException(
                    new AggregateNotFoundException($userId)
                )
            );

        $expectedUsedLabelsMemory = UsedLabelsMemory::create($userId);
        $expectedUsedLabelsMemory->labelUsed($label);

        $this->repository->expects($this->once())
            ->method('add')
            ->with($expectedUsedLabelsMemory);

        $this->service->rememberLabelUsed($userId, $label);
    }

    /**
     * @test
     */
    public function it_gives_me_the_memory_of_a_particular_user()
    {
        $userId = 3;

        $expectedUsedLabelsMemory = new UsedLabelsMemory();
        $expectedUsedLabelsMemory->labelUsed(new Label('foo'));
        $expectedUsedLabelsMemory->labelUsed(new Label('bar'));

        $this->repository->expects($this->once())
            ->method('load')
            ->with($userId)
            ->will(
                $this->returnValue(
                    $expectedUsedLabelsMemory
                )
            );

        $usedLabelsMemory = $this->service->getMemory($userId);

        $this->assertEquals(
            $expectedUsedLabelsMemory,
            $usedLabelsMemory
        );
    }
}
