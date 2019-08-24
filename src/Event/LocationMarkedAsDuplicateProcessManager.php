<?php

declare(strict_types=1);

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Event\Commands\UpdateLocation;
use CultuurNet\UDB3\Event\ReadModel\Relations\RepositoryInterface;
use CultuurNet\UDB3\Event\ValueObjects\LocationId;
use CultuurNet\UDB3\Place\Events\MarkedAsDuplicate;

final class LocationMarkedAsDuplicateProcessManager implements EventListenerInterface
{
    /**
     * @var RepositoryInterface
     */
    private $relationsRepository;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    public function __construct(
        RepositoryInterface $relationsRepository,
        CommandBusInterface $commandBus
    ) {
        $this->relationsRepository = $relationsRepository;
        $this->commandBus = $commandBus;
    }

    public function handle(DomainMessage $domainMessage)
    {
        $domainEvent = $domainMessage->getPayload();

        // Only handle (Place)MarkedAsDuplicate events.
        if (!($domainEvent instanceof MarkedAsDuplicate)) {
            return;
        }

        $duplicatePlaceId = $domainEvent->getPlaceId();
        $canonicalPlaceId = $domainEvent->getDuplicateOf();

        $eventIds = $this->relationsRepository->getEventsLocatedAtPlace($duplicatePlaceId);

        foreach ($eventIds as $eventId) {
            $this->commandBus->dispatch(
                new UpdateLocation($eventId, new LocationId($canonicalPlaceId))
            );
        }
    }
}
