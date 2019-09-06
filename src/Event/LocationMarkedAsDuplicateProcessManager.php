<?php

declare(strict_types=1);

namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Event\Commands\UpdateLocation;
use CultuurNet\UDB3\Event\ValueObjects\LocationId;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Place\Events\MarkedAsDuplicate;
use CultuurNet\UDB3\Search\ResultsGeneratorInterface;

final class LocationMarkedAsDuplicateProcessManager implements EventListenerInterface
{
    /**
     * @var ResultsGeneratorInterface
     */
    private $searchResultsGenerator;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    public function __construct(
        ResultsGeneratorInterface $searchResultsGenerator,
        CommandBusInterface $commandBus
    ) {
        $this->searchResultsGenerator = $searchResultsGenerator;
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

        $query = "location.id:{$duplicatePlaceId}";
        $results = $this->searchResultsGenerator->search($query);

        /* @var IriOfferIdentifier $result */
        foreach ($results as $result) {
            if (!$result->getType()->sameValueAs(OfferType::EVENT())) {
                continue;
            }

            $this->commandBus->dispatch(
                new UpdateLocation($result->getId(), new LocationId($canonicalPlaceId))
            );
        }
    }
}
