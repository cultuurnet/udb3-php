<?php

namespace CultuurNet\UDB3\Label\Services;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Label\Commands\AbstractCommand;
use CultuurNet\UDB3\Label\Commands\Create;
use CultuurNet\UDB3\Label\Commands\MakeInvisible;
use CultuurNet\UDB3\Label\Commands\MakePrivate;
use CultuurNet\UDB3\Label\Commands\MakePublic;
use CultuurNet\UDB3\Label\Commands\MakeVisible;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

class WriteService implements WriteServiceInterface
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * WriteService constructor.
     * @param CommandBusInterface $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     */
    public function __construct(
        CommandBusInterface $commandBus,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->commandBus = $commandBus;
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * @inheritdoc
     */
    public function create(
        LabelName $name,
        Visibility $visibility,
        Privacy $privacy
    ) {
        $uuid = new UUID($this->uuidGenerator->generate());

        $command = new Create(
            $uuid,
            $name,
            $visibility,
            $privacy
        );

        return $this->dispatch($command);
    }

    /**
     * @param UUID $uuid
     * @return WriteResult
     */
    public function makeVisible(UUID $uuid)
    {
        return $this->dispatch(new MakeVisible($uuid));
    }

    /**
     * @param UUID $uuid
     * @return WriteResult
     */
    public function makeInvisible(UUID $uuid)
    {
        return $this->dispatch(new MakeInvisible($uuid));
    }

    /**
     * @param UUID $uuid
     * @return WriteResult
     */
    public function makePublic(UUID $uuid)
    {
        return $this->dispatch(new MakePublic($uuid));
    }

    /**
     * @param UUID $uuid
     * @return WriteResult
     */
    public function makePrivate(UUID $uuid)
    {
        return $this->dispatch(new MakePrivate($uuid));
    }

    /**
     * @param AbstractCommand $command
     * @return WriteResult
     */
    private function dispatch(AbstractCommand $command)
    {
        $commandId = $this->commandBus->dispatch($command);

        return new WriteResult(
            new StringLiteral($commandId),
            $command->getUuid()
        );
    }
}
