<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\CommandHandling;


use Broadway\CommandHandling\CommandBusInterface;
use Broadway\EventDispatcher\EventDispatcherInterface;
use Broadway\Domain\Metadata;

class ResqueCommandBusTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var CommandBusInterface|ContextAwareInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $decoratedCommandBus;

    /**
     * @var ResqueCommandBus
     */
    protected $commandBus;

    /**
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dispatcher;

    public function setUp()
    {
        $queueName = 'test';

        $this->decoratedCommandBus = $this->getMock(
            '\\CultuurNet\\UDB3\\CommandHandling\\TestContextAwareCommandBus'
        );

        $this->dispatcher = $this->getMock(
            '\\Broadway\\EventDispatcher\\EventDispatcherInterface'
        );

        $this->commandBus = new ResqueCommandBus(
            $this->decoratedCommandBus,
            $queueName,
            $this->dispatcher
        );
    }

    /**
     * @test
     */
    public function it_passes_its_context_to_the_decorated_context_aware_command_bus(
    )
    {
        $context = new Metadata(
            [
                'user_id' => 1,
                'user_nick' => 'admin'
            ]
        );

        $this->decoratedCommandBus->expects($this->once())
            ->method('setContext')
            ->with($context);

        $this->commandBus->setContext($context);
    }

    /**
     * @test
     */
    public function on_deferred_dispatch_it_dispatches_the_command_to_the_decorated_command_bus(
    )
    {
        $command = new \stdClass();
        $command->foo = 'bar';

        $this->decoratedCommandBus->expects($this->once())
            ->method('dispatch')
            ->with($command);

        $this->commandBus->deferredDispatch($command);
    }

    /**
     * @test
     */
    public function after_deferred_dispatch_it_resets_the_context_of_the_decorated_context_aware_command_bus(
    )
    {
        $command = new \stdClass();
        $command->target = 'foo';

        $context = new Metadata(
            [
                'user_id' => 1,
                'user_nick' => 'admin'
            ]
        );

        $this->commandBus->setContext($context);

        $this->decoratedCommandBus->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->id('dispatched');

        $this->decoratedCommandBus->expects($this->once())
            ->method('setContext')
            ->with(null)
            ->after('dispatched');

        $this->commandBus->deferredDispatch($command);
    }

    /**
     * @test
     */
    public function after_deferred_dispatch_even_after_exceptions_it_resets_the_context_of_the_decorated_context_aware_command_bus(
    )
    {
        $exception = new \Exception(
            'Something went wrong in the decorated command bus'
        );

        $command = new \stdClass();
        $command->target = 'foo';

        $context = new Metadata(
            [
                'user_id' => 1,
                'user_nick' => 'admin'
            ]
        );

        $this->commandBus->setContext($context);

        $this->decoratedCommandBus->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->willThrowException(
                $exception
            )
            ->id('dispatched');

        $this->decoratedCommandBus->expects($this->once())
            ->method('setContext')
            ->with(null)
            ->after('dispatched');

        $this->setExpectedException(
            get_class($exception),
            $exception->getMessage()
        );

        $this->commandBus->deferredDispatch($command);
    }

    /**
     * @test
     */
    public function it_emits_context_changes_to_the_event_dispatcher()
    {
        $context = new Metadata(
            [
                'user_id' => 1,
                'user_nick' => 'admin'
            ]
        );

        $this->dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [
                    ResqueCommandBus::EVENT_COMMAND_CONTEXT_SET,
                    ['context' => $context]
                ],
                [
                    ResqueCommandBus::EVENT_COMMAND_CONTEXT_SET,
                    ['context' => null]
                ]
            );

        $this->commandBus->setContext($context);

        $command = new \stdClass();
        $command->foo = 'bar';

        $this->commandBus->deferredDispatch($command);
    }
}

abstract class TestContextAwareCommandBus implements CommandBusInterface, ContextAwareInterface
{

}
