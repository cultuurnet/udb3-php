<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Command;

use Broadway\CommandHandling\CommandHandler;
use CultuurNet\UDB3\Variations\EventVariationServiceInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class EventVariationCommandHandler extends CommandHandler Implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var EventVariationServiceInterface
     */
    private $variationService;

    public function __construct(
        EventVariationServiceInterface $variationService
    ) {
        $this->variationService = $variationService;
    }

    protected function handleCreateEventVariation(
        CreateEventVariation $createEventVariation
    ) {
        $variation = $this->variationService->createEventVariation(
            $createEventVariation->getEventUrl(),
            $createEventVariation->getOwnerId(),
            $createEventVariation->getPurpose(),
            $createEventVariation->getDescription()
        );

        if ($this->logger) {
            $this->logger->info(
                'job_info',
                [
                    'event_variation_id' => $variation->getAggregateRootId(),
                ]
            );
        }
    }
}
