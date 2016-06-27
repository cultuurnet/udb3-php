<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON;

use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Label\Events\MadeVisible;
use CultuurNet\UDB3\Label\Label;
use CultuurNet\UDB3\Label\LabelRepository;
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

    public function handleMadeVisible(MadeVisible $madeVisible)
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

                    // TODO: unlike CDBXML, the json-ld labels are just strings so we can only make sure it's in the list
                    $labels[] = $labelName;
                    $offerLd->labels = array_unique($labels);

                    $this->offerRepository->save($offerDocument->withBody($offerLd));
                }
            } catch (DocumentGoneException $exception) {
                //TODO: you don't want to stop publishing the label for all documents if one is missing but maybe log it?
            }
        }
    }
}
