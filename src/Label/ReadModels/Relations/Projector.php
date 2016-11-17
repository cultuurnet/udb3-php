<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations;

use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Label\LabelEventRelationTypeResolverInterface;
use CultuurNet\UDB3\Label\ReadModels\AbstractProjector;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\LabelRelation;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\WriteRepositoryInterface;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent as OfferAbstractLabelEvent;
use CultuurNet\UDB3\Organizer\Events\AbstractLabelEvent as OrganizerAbstractLabelEvent;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use ValueObjects\Identity\UUID;
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
        $LabelRelation = $this->createLabelRelation($labelAdded, $metadata);

        try {
            if (!is_null($LabelRelation)) {
                $this->writeRepository->save(
                    $LabelRelation->getUuid(),
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
        $labelRelation = $this->createLabelRelation($labelDeleted, $metadata);

        if (!is_null($labelRelation)) {
            $this->writeRepository->deleteByUuidAndRelationId(
                $labelRelation->getUuid(),
                new StringLiteral($labelDeleted->getItemId())
            );
        }
    }

    /**
     * @param OfferAbstractLabelEvent|OrganizerAbstractLabelEvent $labelEvent
     * @param Metadata $metadata
     * @return LabelRelation
     */
    private function createLabelRelation($labelEvent, Metadata $metadata)
    {
        $labelRelation = null;

        $metadataArray = $metadata->serialize();

        $uuid = isset($metadataArray['labelUuid']) ? new UUID($metadataArray['labelUuid']) : null;
        $relationType = $this->offerTypeResolver->getRelationType($labelEvent);
        $relationId = new StringLiteral($labelEvent->getItemId());

        if (!is_null($uuid)) {
            $labelRelation = new LabelRelation(
                $uuid,
                $relationType,
                $relationId
            );
        }

        return $labelRelation;
    }
}
