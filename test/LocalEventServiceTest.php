<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;


use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;

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

    public function setUp()
    {
        $this->documentRepository = $this->getMock('\\CultuurNet\\UDB3\\Event\\ReadModel\\DocumentRepositoryInterface');
        $this->eventRepository = $this->getMock('Broadway\\Repository\\RepositoryInterface');
        $this->eventService = new LocalEventService(
            $this->documentRepository,
            $this->eventRepository
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

        $this->setExpectedException('\\CultuurNet\\UDB3\\EventNotFoundException', 'Event with id: ' . $id . ' not found');

        $this->eventService->getEvent($id);


    }
} 
