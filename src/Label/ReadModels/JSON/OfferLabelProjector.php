<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Label\Events\AbstractEvent;
use CultuurNet\UDB3\Label\Events\MadeInvisible;
use CultuurNet\UDB3\Label\Events\MadeVisible;
use CultuurNet\UDB3\Label\LabelDomainMessageEnricher;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use ValueObjects\String\String as StringLiteral;

class OfferLabelProjector implements EventListenerInterface, LoggerAwareInterface
{
    use DelegateEventHandlingToSpecificMethodTrait {
        DelegateEventHandlingToSpecificMethodTrait::handle as handleSpecific;
    }

    use LoggerAwareTrait;

    /**
     * @var ReadRepositoryInterface
     */
    private $relationRepository;

    /**
     * @var DocumentRepositoryInterface
     */
    private $offerRepository;

    /**
     * OfferLabelProjector constructor.
     * @param DocumentRepositoryInterface $offerRepository
     * @param ReadRepositoryInterface $relationRepository
     */
    public function __construct(
        DocumentRepositoryInterface $offerRepository,
        ReadRepositoryInterface $relationRepository
    ) {
        $this->offerRepository = $offerRepository;
        $this->relationRepository = $relationRepository;
        $this->logger = new NullLogger();
    }

    /**
     * @param DomainMessage $domainMessage
     */
    public function handle(DomainMessage $domainMessage)
    {
        $event = $domainMessage->getPayload();

        if ($event instanceof MadeVisible) {
            $this->applyMadeVisible($domainMessage->getPayload(), $domainMessage->getMetadata());
        } else if ($event instanceof MadeInvisible) {
            $this->applyMadeInvisible($domainMessage->getPayload(), $domainMessage->getMetadata());
        } else {
            $this->handleSpecific($domainMessage);
        }
    }

    /**
     * @param MadeVisible $madeVisible
     * @param Metadata $metaData
     */
    public function applyMadeVisible(MadeVisible $madeVisible, Metadata $metaData)
    {
        $offers = $this->getRelatedOffers($madeVisible);
        $labelName = $this->getLabelName($metaData);

        foreach ($offers as $offer) {
            $offerLd = $offer->getBody();

            $labels = isset($offerLd->labels) ? $offerLd->labels : [];

            // The label should now be shown so we add it to the list of regular labels.
            $labels[] = $labelName;
            $offerLd->labels = array_unique($labels);

            // Another list tracks hidden labels so we have to make sure it's no longer listed here.
            if (isset($offerLd->hiddenLabels)) {
                $offerLd->hiddenLabels = array_diff($offerLd->hiddenLabels, [$labelName]);

                // If there are no other hidden labels left, remove the list so we don't have an empty leftover attribute.
                if (count($offerLd->hiddenLabels) === 0) {
                    unset($offerLd->hiddenLabels);
                }
            }

            $this->offerRepository->save($offer->withBody($offerLd));
        }
    }

    /**
     * @param MadeInvisible $madeInvisible
     * @param Metadata $metaData
     */
    public function applyMadeInvisible(MadeInvisible $madeInvisible, Metadata $metaData)
    {
        $offers = $this->getRelatedOffers($madeInvisible);
        $labelName = $this->getLabelName($metaData);

        foreach ($offers as $offer) {
            $offerLd = $offer->getBody();

            $hiddenLabels = isset($offerLd->hiddenLabels) ? $offerLd->hiddenLabels : [];

            // The label is now invisible so we add it to the list of hidden labels.
            $hiddenLabels[] = $labelName;
            $offerLd->hiddenLabels = array_unique($hiddenLabels);

            // Remove the hidden label from the list of visible labels.
            if (isset($offerLd->labels)) {
                $offerLd->labels = array_diff($offerLd->labels, [$labelName]);

                // If there are no visible labels left, remove the list so we don't have an empty leftover attribute.
                if (count($offerLd->labels) === 0) {
                    unset($offerLd->labels);
                }
            }

            $this->offerRepository->save($offer->withBody($offerLd));
        }
    }

    /**
     * @param AbstractEvent $labelEvent
     * @return \Generator|JsonDocument[]
     */
    private function getRelatedOffers(AbstractEvent $labelEvent)
    {
        $offerRelations = $this->relationRepository->getOfferLabelRelations($labelEvent->getUuid());

        foreach ($offerRelations as $offerRelation) {
            try {
                $offer = $this->offerRepository->get((string) $offerRelation->getRelationId());

                if ($offer) {
                    yield $offer;
                }
            } catch (DocumentGoneException $exception) {
                $this->logger->alert(
                    'Can not update visibility of label: "'. $offerRelation->getUuid() . '"'
                    . ' for the offer with id: "' . $offerRelation->getRelationId() . '"'
                    . ' because the document could not be retrieved.'
                );
            }
        }
    }

    /**
     * @param Metadata $metadata
     * @return string|null
     */
    private function getLabelName(Metadata $metadata)
    {
        $metadataAsArray = $metadata->serialize();

        return isset($metadataAsArray[LabelDomainMessageEnricher::LABEL_NAME]) ?
            $metadataAsArray[LabelDomainMessageEnricher::LABEL_NAME] : null;
    }
}
