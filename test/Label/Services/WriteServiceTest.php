<?php

namespace CultuurNet\UDB3\Label\Services;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Label\Commands\Create;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    const COMMAND_ID = 'commandId';

    /**
     * @var Create
     */
    private $create;

    /**
     * @var CommandBusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commandBus;

    /**
     * @var UuidGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uuidGenerator;

    /**
     * @var WriteService
     */
    private $writeService;

    protected function setUp()
    {
        $this->create = new Create(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::INVISIBLE(),
            Privacy::PRIVACY_PRIVATE()
        );

        $this->commandBus = $this->getMock(CommandBusInterface::class);
        $this->mockDispatch();

        $this->uuidGenerator = $this->getMock(UuidGeneratorInterface::class);
        $this->mockGenerate();

        $this->writeService = new WriteService(
            $this->commandBus,
            $this->uuidGenerator
        );
    }

    /**
     * @test
     */
    public function it_calls_dispatch_with_create_command_for_create()
    {
        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with($this->create);

        $this->writeService->create(
            $this->create->getName(),
            $this->create->getVisibility(),
            $this->create->getPrivacy()
        );
    }

    /**
     * @test
     */
    public function it_returns_write_result_for_create()
    {
        $expectedWriteResult = new WriteResult(
            new StringLiteral(self::COMMAND_ID),
            $this->create->getUuid()
        );

        $writeResult = $this->writeService->create(
            $this->create->getName(),
            $this->create->getVisibility(),
            $this->create->getPrivacy()
        );

        $this->assertEquals($expectedWriteResult, $writeResult);
    }

    private function mockGenerate()
    {
        $this->uuidGenerator->method('generate')
            ->willReturn($this->create->getUuid()->toNative());
    }

    private function mockDispatch()
    {
        $this->commandBus->method('dispatch')
            ->willReturn(self::COMMAND_ID);
    }
}
