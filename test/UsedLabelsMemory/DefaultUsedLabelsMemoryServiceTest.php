<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UsedLabelsMemory;

use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
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
     * @var TraceableEventStore
     */
    protected $eventStore;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->eventStore = new TraceableEventStore(
            new InMemoryEventStore()
        );

        $this->repository = new UsedLabelsMemoryRepository(
            $this->eventStore,
            new SimpleEventBus()
        );

        $this->service = new DefaultUsedLabelsMemoryService(
            $this->repository
        );
    }

    /**
     * @test
     */
    public function it_remembers_labels_per_user_in_a_repository()
    {
        $userA = 1;
        $userB = 2;

        $label = new Label('classical rock');
        $secondLabel = new Label('alternative');

        $this->service->rememberLabelUsed($userB, $label);

        $this->eventStore->trace();

        $this->service->rememberLabelUsed($userA, $label);
        $this->service->rememberLabelUsed($userB, $secondLabel);

        $this->assertEquals(
            [
                new Created(
                    $userA
                ),
                new LabelUsed(
                    $userA,
                    $label
                ),
                new LabelUsed(
                    $userB,
                    $secondLabel
                )
            ],
            $this->eventStore->getEvents()
        );
    }

    /**
     * @test
     */
    public function it_gives_me_the_memory_of_a_particular_user()
    {
        $userId = 3;
        $otherUserId = 5;

        $this->service->rememberLabelUsed($userId, new Label('foo'));
        $this->service->rememberLabelUsed($userId, new Label('bar'));
        $this->service->rememberLabelUsed($otherUserId, new Label('lorem'));

        $usedLabelsMemory = $this->service->getMemory($userId);

        $this->assertEquals(
            [
                new Label('bar'),
                new Label('foo'),
            ],
            $usedLabelsMemory->getLabels()
        );
    }
}
