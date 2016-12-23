<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Event\EventNotFoundException;
use CultuurNet\UDB3\Event\LocalEventService;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;

class LocalEventServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocalEventService
     */
    protected $eventService;

    /**
     * @var DocumentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentRepository;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventRepository;

    /**
     * @var IriGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $iriGenerator;

    /**
     * @var \CultuurNet\UDB3\Event\ReadModel\Relations\RepositoryInterface||\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventRelationsRepository;

    public function setUp()
    {
        $this->documentRepository = $this->createMock(DocumentRepositoryInterface::class);
        $this->eventRepository = $this->createMock(RepositoryInterface::class);
        $this->eventRelationsRepository = $this->createMock(Event\ReadModel\Relations\RepositoryInterface::class);

        $this->iriGenerator = $this->createMock(IriGeneratorInterface::class);
        $this->iriGenerator->expects($this->any())
            ->method('iri')
            ->willReturnCallback(
                function ($id) {
                    return "event/{$id}";
                }
            );

        $this->eventService = new LocalEventService(
            $this->documentRepository,
            $this->eventRepository,
            $this->eventRelationsRepository,
            $this->iriGenerator
        );
    }

    /**
     * @test
     */
    public function it_throws_an_EventNotFoundException_when_aggregate_is_not_found_in_event_repository()
    {
        $id = 'some-unknown-id';

        $this->eventRepository->expects($this->once())
            ->method('load')
            ->with($id)
            ->willThrowException(new AggregateNotFoundException());

        $this->setExpectedException(EventNotFoundException::class, 'Event with id: ' . $id . ' not found');

        $this->eventService->getEvent($id);
    }
}
