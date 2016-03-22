<?php

namespace CultuurNet\UDB3\ReadModel;

use Broadway\EventHandling\EventBusInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;

class BroadcastingDocumentRepositoryDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventBusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventBus;

    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $decoratedRepository;

    /**
     * @var BroadcastingDocumentRepositoryDecorator
     */
    protected $repository;

    /**
     * @var DocumentEventFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventFactory;

    public function setUp()
    {
        $this->decoratedRepository = $this->getMock(DocumentRepositoryInterface::class);
        $this->eventBus = $this->getMock(EventBusInterface::class);
        $this->eventFactory = $this->getMock(DocumentEventFactory::class);

        $this->repository = new BroadcastingDocumentRepositoryDecorator(
            $this->decoratedRepository,
            $this->eventBus,
            $this->eventFactory
        );
    }

    /**
     * @test
     */
    public function it_broadcasts_when_a_document_is_saved()
    {
        $document = new JsonDocument('some-document-id', '{"nice":"body"}');

        // the provided factory should be used to create a new event
        $this->eventFactory->expects($this->once())
            ->method('createEvent')
            ->with('some-document-id');

        // when saving the event it should also save the document in the decorated repository
        $this->decoratedRepository->expects($this->once())
            ->method('save')
            ->with($document);

        $this->eventBus->expects($this->once())
            ->method('publish');

        $this->repository->save($document);
    }
}
