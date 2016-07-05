<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON;

use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Label\Events\AbstractEvent;
use CultuurNet\UDB3\Label\Events\MadeInvisible;
use CultuurNet\UDB3\Label\Events\MadeVisible;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\OfferLabelRelation;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\ReadRepositoryInterface;
use Generator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class OfferLabelProjector implements EventListenerInterface, LoggerAwareInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;
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

    public function applyMadeVisible(MadeVisible $madeVisible)
    {
        $relatedDocuments = $this->getRelatedDocuments($madeVisible);
        
        foreach ($relatedDocuments as $relatedDocument) {
            $offerDocument = $relatedDocument->getJsonDocument();

            $labelName = $relatedDocument->getLabelName();
            $offerLd = $offerDocument->getBody();

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

            $this->offerRepository->save($offerDocument->withBody($offerLd));
        }
    }

    public function applyMadeInvisible(MadeInvisible $madeInvisible)
    {
        $relatedDocuments = $this->getRelatedDocuments($madeInvisible);

        foreach ($relatedDocuments as $relatedDocument) {
            $offerDocument = $relatedDocument->getJsonDocument();

            $labelName = $relatedDocument->getLabelName();
            $offerLd = $offerDocument->getBody();

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

            $this->offerRepository->save($offerDocument->withBody($offerLd));
        }
    }

    /**
     * @param AbstractEvent $labelEvent
     * @return Generator|RelatedDocument[]
     */
    private function getRelatedDocuments(AbstractEvent $labelEvent)
    {
        /** @var OfferLabelRelation[] $offerRelations */
        $offerRelations = $this->relationRepository->getOfferLabelRelations($labelEvent->getUuid());

        foreach ($offerRelations as $offerRelation) {
            try {
                $offerDocument = $this->offerRepository->get((string) $offerRelation->getRelationId());

                if ($offerDocument) {
                    yield new RelatedDocument($offerRelation, $offerDocument);
                }
            } catch (DocumentGoneException $exception) {
                $this->logger->alert(
                    'Can not update visibility of label: "'. $offerRelation->getLabelName() . '"'
                    . ' for the offer with id: "' . $offerRelation->getRelationId() . '"'
                    . ' because the document could not be retrieved.'
                );
            }
        }
    }
}
