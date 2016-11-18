<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations;

use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Label\LabelEventRelationTypeResolverInterface;
use CultuurNet\UDB3\Label\ReadModels\AbstractProjector;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\LabelRelation;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\WriteRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent as OfferAbstractLabelEvent;
use CultuurNet\UDB3\Organizer\Events\AbstractLabelEvent as OrganizerAbstractLabelEvent;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use ValueObjects\String\String as StringLiteral;

class Projector extends AbstractProjector
{
    /**
     * @var WriteRepositoryInterface
     */
    private $writeRepository;

    /**
     * @var LabelEventRelationTypeResolverInterface
     */
    private $offerTypeResolver;

    /**
     * Projector constructor.
     * @param WriteRepositoryInterface $writeRepository
     * @param LabelEventRelationTypeResolverInterface $labelEventOfferTypeResolver
     */
    public function __construct(
        WriteRepositoryInterface $writeRepository,
        LabelEventRelationTypeResolverInterface $labelEventOfferTypeResolver
    ) {
        $this->writeRepository = $writeRepository;
        $this->offerTypeResolver = $labelEventOfferTypeResolver;

    }

    /**
     * @inheritdoc
     */
    public function applyLabelAdded($labelAdded, Metadata $metadata)
    {
        $LabelRelation = $this->createLabelRelation($labelAdded);

        try {
            if (!is_null($LabelRelation)) {
                $this->writeRepository->save(
                    $LabelRelation->getLabelName(),
                    $LabelRelation->getRelationType(),
                    $LabelRelation->getRelationId()
                );
            }
        } catch (UniqueConstraintViolationException $exception) {
            // By design to catch unique exception.
        }
    }

    /**
     * @inheritdoc
     */
    public function applyLabelDeleted($labelDeleted, Metadata $metadata)
    {
        $labelRelation = $this->createLabelRelation($labelDeleted);

        if (!is_null($labelRelation)) {
            $this->writeRepository->deleteByLabelNameAndRelationId(
                $labelRelation->getLabelName(),
                new StringLiteral($labelDeleted->getItemId())
            );
        }
    }

    /**
     * @param OfferAbstractLabelEvent|OrganizerAbstractLabelEvent $labelEvent
     * @return LabelRelation
     */
    private function createLabelRelation($labelEvent)
    {
        $labelRelation = null;

        $labelName = new LabelName((string) $labelEvent->getLabel());
        $relationType = $this->offerTypeResolver->getRelationType($labelEvent);
        $relationId = new StringLiteral($labelEvent->getItemId());

        $labelRelation = new LabelRelation(
            $labelName,
            $relationType,
            $relationId
        );

        return $labelRelation;
    }
}
