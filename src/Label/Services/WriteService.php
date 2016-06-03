<?php

namespace CultuurNet\UDB3\Label\Services;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Label\Commands\Create;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

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
        StringLiteral $name,
        Visibility $visibility,
        Privacy $privacy
    ) {
        $uuid = new UUID($this->uuidGenerator->generate());

        $commandId = $this->commandBus->dispatch(new Create(
            $uuid,
            $name,
            $visibility,
            $privacy
        ));

        return new WriteResult(new StringLiteral($commandId), $uuid);
    }
}
