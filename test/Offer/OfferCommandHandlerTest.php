<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\EventStoreInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Item\Commands\AddLabel;
use CultuurNet\UDB3\Offer\Item\Commands\DeleteLabel;
use CultuurNet\UDB3\Offer\Item\Events\ItemCreated;
use CultuurNet\UDB3\Offer\Item\Events\LabelAdded;
use CultuurNet\UDB3\Offer\Item\Events\LabelDeleted;
use CultuurNet\UDB3\Offer\Item\ItemCommandHandler;
use CultuurNet\UDB3\Offer\Item\ItemRepository;
use CultuurNet\UDB3\Offer\Mock\Commands\AddLabel as AddLabelToSomethingElse;
use CultuurNet\UDB3\Offer\Mock\Commands\DeleteLabel as DeleteLabelFromSomethingElse;

class OfferCommandHandlerTest extends CommandHandlerScenarioTestCase
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Label
     */
    protected $label;

    /**
     * @var ItemCreated
     */
    protected $itemCreated;

    public function setUp()
    {
        parent::setUp();

        $this->id = '123';
        $this->label = new Label('foo');

        $this->itemCreated = new ItemCreated($this->id);
    }

    protected function createCommandHandler(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus
    ) {
        return new ItemCommandHandler(
            new ItemRepository($eventStore, $eventBus)
        );
    }

    /**
     * @test
     */
    public function it_handles_add_label_commands_from_the_correct_namespace()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->itemCreated
                ]
            )
            ->when(
                new AddLabel($this->id, $this->label)
            )
            ->then(
                [
                    new LabelAdded($this->id, $this->label)
                ]
            );
    }

    /**
     * @test
     */
    public function it_ignores_add_label_commands_from_incorrect_namespaces()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->itemCreated
                ]
            )
            ->when(
                new AddLabelToSomethingElse($this->id, $this->label)
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_handles_delete_label_commands_from_the_correct_namespace()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->itemCreated,
                    new LabelAdded($this->id, $this->label),
                ]
            )
            ->when(
                new DeleteLabel($this->id, $this->label)
            )
            ->then(
                [
                    new LabelDeleted($this->id, $this->label)
                ]
            );
    }

    /**
     * @test
     */
    public function it_ignores_delete_label_commands_from_incorrect_namespaces()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->itemCreated,
                    new LabelAdded($this->id, $this->label),
                ]
            )
            ->when(
                new DeleteLabelFromSomethingElse($this->id, $this->label)
            )
            ->then([]);
    }
}
