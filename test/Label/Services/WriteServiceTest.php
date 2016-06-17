<?php

namespace CultuurNet\UDB3\Label\Services;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Label\Commands\Create;
use CultuurNet\UDB3\Label\Commands\MakeInvisible;
use CultuurNet\UDB3\Label\Commands\MakePrivate;
use CultuurNet\UDB3\Label\Commands\MakePublic;
use CultuurNet\UDB3\Label\Commands\MakeVisible;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    const COMMAND_ID = 'commandId';

    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var Create
     */
    private $create;

    /**
     * @var WriteResult
     */
    private $expectedWriteResult;

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
        $this->uuid = new UUID();

        $this->create = new Create(
            $this->uuid,
            new StringLiteral('labelName'),
            Visibility::INVISIBLE(),
            Privacy::PRIVACY_PRIVATE()
        );

        $this->expectedWriteResult = new WriteResult(
            new StringLiteral(self::COMMAND_ID),
            $this->uuid
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
        $writeResult = $this->writeService->create(
            $this->create->getName(),
            $this->create->getVisibility(),
            $this->create->getPrivacy()
        );

        $this->assertEquals($this->expectedWriteResult, $writeResult);
    }

    /**
     * @test
     */
    public function it_calls_dispatch_with_make_visible_command_for_make_visible()
    {
        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with(new MakeVisible($this->uuid));

        $this->writeService->makeVisible($this->uuid);
    }

    /**
     * @test
     */
    public function it_returns_write_result_for_make_visible()
    {
        $writeResult = $this->writeService->makeVisible($this->uuid);

        $this->assertEquals($this->expectedWriteResult, $writeResult);
    }

    /**
     * @test
     */
    public function it_calls_dispatch_with_make_invisible_command_for_make_invisible()
    {
        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with(new MakeInvisible($this->uuid));

        $this->writeService->makeInvisible($this->uuid);
    }

    /**
     * @test
     */
    public function it_returns_write_result_for_make_invisible()
    {
        $writeResult = $this->writeService->makeInvisible($this->uuid);

        $this->assertEquals($this->expectedWriteResult, $writeResult);
    }

    /**
     * @test
     */
    public function it_calls_dispatch_with_make_public_command_for_make_public()
    {
        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with(new MakePublic($this->uuid));

        $this->writeService->makePublic($this->uuid);
    }

    /**
     * @test
     */
    public function it_returns_write_result_for_make_public()
    {
        $writeResult = $this->writeService->makePublic($this->uuid);

        $this->assertEquals($this->expectedWriteResult, $writeResult);
    }

    /**
     * @test
     */
    public function it_calls_dispatch_with_make_private_command_for_make_private()
    {
        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with(new MakePrivate($this->uuid));

        $this->writeService->makePrivate($this->uuid);
    }

    /**
     * @test
     */
    public function it_returns_write_result_for_make_private()
    {
        $writeResult = $this->writeService->makePrivate($this->uuid);

        $this->assertEquals($this->expectedWriteResult, $writeResult);
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
