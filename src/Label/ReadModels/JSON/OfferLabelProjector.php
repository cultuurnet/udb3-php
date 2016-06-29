<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON;

use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Label\Events\MadeVisible;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\OfferLabelRelation;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\ReadRepositoryInterface;

class OfferLabelProjector
{
    use DelegateEventHandlingToSpecificMethodTrait;

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
    }

    public function applyMadeVisible(MadeVisible $madeVisible)
    {
        /** @var OfferLabelRelation[] $offerRelations */
        $offerRelations = $this->relationRepository->getOfferLabelRelations($madeVisible->getUuid());

        foreach ($offerRelations as $offerRelation) {
            try {
                $offerDocument = $this->offerRepository->get((string) $offerRelation->getRelationId());

                if ($offerDocument) {
                    $offerLd = $offerDocument->getBody();

                    $labels = isset($offerLd->labels) ? $offerLd->labels : [];
                    $labelName = (string) $offerRelation->getLabelName();

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
            } catch (DocumentGoneException $exception) {
                //TODO: you don't want to stop publishing the label for all documents if one is missing but maybe log it?
            }
        }
    }
}
