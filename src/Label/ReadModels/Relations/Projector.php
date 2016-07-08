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
        $entity = $this->createEntity($labelAdded, $metadata);

        try {
            if (!is_null($entity)) {
                $this->writeRepository->save(
                    $entity->getUuid(),
                    $entity->getLabelName(),
                    $entity->getRelationType(),
                    $entity->getRelationId()
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
        $entity = $this->createEntity($labelDeleted, $metadata);

        if (!is_null($entity)) {
            $this->writeRepository->deleteByUuidAndRelationId(
                $entity->getUuid(),
                new StringLiteral($labelDeleted->getItemId())
            );
        }
    }

    /**
     * @param AbstractLabelEvent $labelEvent
     * @param Metadata $metadata
     * @return OfferLabelRelation
     */
    private function createEntity(
        AbstractLabelEvent $labelEvent,
        Metadata $metadata
    ) {
        $entity = null;

        $metadataArray = $metadata->serialize();

        $uuid = isset($metadataArray['labelUuid']) ? new UUID($metadataArray['labelUuid']) : null;
        $relationType = $this->offerTypeResolver->getOfferType($labelEvent);
        $relationId = new StringLiteral($labelEvent->getItemId());

        if (!is_null($uuid)) {
            $entity = new OfferLabelRelation(
                $uuid,
                new StringLiteral((string) $labelEvent->getLabel()),
                $relationType,
                $relationId
            );
        }

        return $entity;
    }
}
