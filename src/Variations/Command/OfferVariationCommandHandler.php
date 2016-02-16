<?php

namespace CultuurNet\UDB3\Variations\Command;

use Broadway\CommandHandling\CommandHandler;
use CultuurNet\UDB3\Variations\OfferVariationServiceInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class OfferVariationCommandHandler extends CommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var EventVariationServiceInterface
     */
    private $variationService;

    public function __construct(
        OfferVariationServiceInterface $variationService
    ) {
        $this->variationService = $variationService;
    }

    protected function handleCreateEventVariation(
        CreateOfferVariation $createEventVariation
    ) {
        $variation = $this->variationService->createOfferVariation(
            $createEventVariation->getOfferUrl(),
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

    protected function handleEditDescription(EditDescription $editDescription)
    {
        $this->variationService->editDescription(
            $editDescription->getId(),
            $editDescription->getDescription()
        );

        if ($this->logger) {
            $this->logger->info(
                'job_info',
                [
                    'event_variation_id' => (string) $editDescription->getId(),
                ]
            );
        }
    }

    protected function handleDeleteEventVariation(DeleteOfferVariation $deleteEventVariation)
    {
        $this->variationService->deleteOfferVariation($deleteEventVariation->getId());

        if ($this->logger) {
            $this->logger->info(
                'job_info',
                [
                    'event_variation_id' => (string) $deleteEventVariation->getId()
                ]
            );
        }
    }
}
