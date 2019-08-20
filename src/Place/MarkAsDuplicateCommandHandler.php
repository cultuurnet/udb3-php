<?php

namespace CultuurNet\UDB3\Place;

use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use CultuurNet\UDB3\Place\Commands\MarkAsDuplicate;

class MarkAsDuplicateCommandHandler extends Udb3CommandHandler
{
    /**
     * @var PlaceRepository
     */
    private $repository;

    public function __construct(PlaceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handleMarkAsDuplicate(MarkAsDuplicate $command): void
    {
        /** @var Place $placeToMarkAsDuplicate */
        $placeToMarkAsDuplicate = $this->repository->load($command->getDuplicatePlaceId());

        /** @var Place $placeToMarkAsMaster */
        $placeToMarkAsMaster = $this->repository->load($command->getMasterPlaceId());

        // TODO: start transaction

        try {
            $placeToMarkAsDuplicate->markAsDuplicateOf($command->getMasterPlaceId());
            $this->repository->save($placeToMarkAsDuplicate);

            $placeToMarkAsMaster->markAsMasterOf($command->getDuplicatePlaceId());
            $this->repository->save($placeToMarkAsMaster);
        } catch (CannotMarkPlaceAsMaster | CannotMarkPlaceAsDuplicate $e) {
            // TODO: rollback transaction
        }

        // TODO: commit transaction
    }
}
