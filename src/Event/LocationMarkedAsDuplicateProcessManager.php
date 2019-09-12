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
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

final class LocationMarkedAsDuplicateProcessManager implements EventListenerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

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
        $this->logger = new NullLogger();
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

        $updated = 0;
        $skipped = [];

        /* @var IriOfferIdentifier $result */
        foreach ($results as $result) {
            if (!$result->getType()->sameValueAs(OfferType::EVENT())) {
                $skipped[] = $result->getId();
                $this->logger->warning(
                    'Skipped result with id ' . $result->getId() . ' because it\'s not an event according to the @id parser.'
                );
                continue;
            }

            $this->commandBus->dispatch(
                new UpdateLocation($result->getId(), new LocationId($canonicalPlaceId))
            );

            $updated++;

            $this->logger->info(
                'Dispatched UpdateLocation for result with id ' . $result->getId()
            );
        }

        $this->logger->info('Received ' . ($updated + count($skipped)) . ' results from the search api.');
        $this->logger->info('Updated ' . $updated . ' events to the canonical location.');
        $this->logger->info(
            'Skipped ' . count($skipped) . ' events:' . PHP_EOL . implode(PHP_EOL, $skipped)
        );
    }
}
