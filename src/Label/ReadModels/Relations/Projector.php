<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations;

use Broadway\Domain\Metadata;
use CultuurNet\UDB3\Label\LabelEventOfferTypeResolverInterface;
use CultuurNet\UDB3\Label\ReadModels\AbstractProjector;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\OfferLabelRelation;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\WriteRepositoryInterface;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;
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
     * @var LabelEventOfferTypeResolverInterface
     */
    private $offerTypeResolver;

    /**
     * Projector constructor.
     * @param WriteRepositoryInterface $writeRepository
     * @param LabelEventOfferTypeResolverInterface $labelEventOfferTypeResolver
     */
    public function __construct(
        WriteRepositoryInterface $writeRepository,
        LabelEventOfferTypeResolverInterface $labelEventOfferTypeResolver
    ) {
        $this->writeRepository = $writeRepository;
        $this->offerTypeResolver = $labelEventOfferTypeResolver;

    }

    /**
     * @inheritdoc
     */
    public function applyLabelAdded(AbstractLabelAdded $labelAdded, Metadata $metadata)
    {
        $offerLabelRelation = $this->createOfferLabelRelation($labelAdded, $metadata);

        try {
            if (!is_null($offerLabelRelation)) {
                $this->writeRepository->save(
                    $offerLabelRelation->getUuid(),
                    $offerLabelRelation->getRelationType(),
                    $offerLabelRelation->getRelationId()
                );
            }
        } catch (UniqueConstraintViolationException $exception) {
            // By design to catch unique exception.
        }
    }

    /**
     * @inheritdoc
     */
    public function applyLabelDeleted(AbstractLabelDeleted $labelDeleted, Metadata $metadata)
    {
        $offerLabelRelation = $this->createOfferLabelRelation($labelDeleted, $metadata);

        if (!is_null($offerLabelRelation)) {
            $this->writeRepository->deleteByUuidAndRelationId(
                $offerLabelRelation->getUuid(),
                new StringLiteral($labelDeleted->getItemId())
            );
        }
    }

    /**
     * @param AbstractLabelEvent $labelEvent
     * @param Metadata $metadata
     * @return OfferLabelRelation
     */
    private function createOfferLabelRelation(
        AbstractLabelEvent $labelEvent,
        Metadata $metadata
    ) {
        $offerLabelRelation = null;

        $metadataArray = $metadata->serialize();

        $uuid = isset($metadataArray['labelUuid']) ? new UUID($metadataArray['labelUuid']) : null;
        $relationType = $this->offerTypeResolver->getOfferType($labelEvent);
        $relationId = new StringLiteral($labelEvent->getItemId());

        if (!is_null($uuid)) {
            $offerLabelRelation = new OfferLabelRelation(
                $uuid,
                $relationType,
                $relationId
            );
        }

        return $offerLabelRelation;
    }
}
