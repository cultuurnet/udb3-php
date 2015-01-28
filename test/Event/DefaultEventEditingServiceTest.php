<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;


use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\EventNotFoundException;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Keyword;
use CultuurNet\UDB3\Language;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\PlaceService;

class DefaultEventEditingServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventEditingServiceInterface
     */
    protected $eventEditingService;

    /**
     * @var EventServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventService;

    /**
     * @var CommandBusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandBus;

    public function setUp()
    {
        $this->eventService = $this->getMock(
            'CultuurNet\\UDB3\\EventServiceInterface'
        );

        $this->commandBus = $this->getMock(
            'Broadway\\CommandHandling\\CommandBusInterface'
        );

        $this->uuidGenerator = $this->getMock(
            UuidGeneratorInterface::class
        );

        $this->eventEditingService = new DefaultEventEditingService(
            $this->eventService,
            $this->commandBus,
            $this->uuidGenerator,
            $this->getMock(RepositoryInterface::class),
            $this->getMock(PlaceService::class, array(), array(), '', false)
        );
    }

    /**
     * @test
     */
    public function it_refuses_to_translate_title_of_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException('CultuurNet\\UDB3\\EventNotFoundException');

        $this->setUpEventNotFound($id);

        $this->eventEditingService->translateTitle($id, new Language('nl'), 'new title');
    }

    /**
     * @test
     */
    public function it_refuses_to_translate_description_of_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException('CultuurNet\\UDB3\\EventNotFoundException');

        $this->setUpEventNotFound($id);

        $this->eventEditingService->translateDescription($id, new Language('nl'), 'new description');
    }

    /**
     * @test
     */
    public function it_refuses_to_tag_an_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException('CultuurNet\\UDB3\\EventNotFoundException');

        $this->setUpEventNotFound($id);

        $this->eventEditingService->tag($id, new Keyword('foo'));
    }

    /**
     * @test
     */
    public function it_refuses_to_erase_a_tag_from_an_unknown_event()
    {
        $id = 'some-unknown-id';

        $this->setExpectedException('CultuurNet\\UDB3\\EventNotFoundException');

        $this->setUpEventNotFound($id);

        $this->eventEditingService->eraseTag($id, new Keyword('foo'));
    }

    private function setUpEventNotFound($id)
    {
        $this->eventService->expects($this->once())
            ->method('getEvent')
            ->with($id)
            ->willThrowException(new EventNotFoundException());
    }
} 
