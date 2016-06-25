<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations;

use CultuurNet\UDB3\Label\ReadModels\AbstractProjector;
use CultuurNet\UDB3\Label\ReadModels\Helper\LabelEventHelper;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\OfferLabelRelation;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\WriteRepositoryInterface;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use ValueObjects\String\String as StringLiteral;

class Projector extends AbstractProjector
{
    /**
     * @var LabelEventHelper
     */
    private $labelEventHelper;

    /**
     * @var WriteRepositoryInterface
     */
    private $writeRepository;

    /**
     * Projector constructor.
     * @param WriteRepositoryInterface $writeRepository
     * @param LabelEventHelper $labelEventHelper
     */
    public function __construct(
        WriteRepositoryInterface $writeRepository,
        LabelEventHelper $labelEventHelper
    ) {
        $this->labelEventHelper = $labelEventHelper;
        $this->writeRepository = $writeRepository;
    }

    /**
     * @inheritdoc
     */
    public function applyLabelAdded(AbstractLabelAdded $labelAdded)
    {
        $entity = $this->createEntity($this->labelEventHelper, $labelAdded);

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
    public function applyLabelDeleted(AbstractLabelDeleted $labelDeleted)
    {
        $entity = $this->createEntity($this->labelEventHelper, $labelDeleted);

        if (!is_null($entity)) {
            $this->writeRepository->deleteByUuidAndRelationId(
                $entity->getUuid(),
                new StringLiteral($labelDeleted->getItemId())
            );
        }
    }

    /**
     * @param LabelEventHelper $labelEventHelper
     * @param AbstractLabelEvent $labelEvent
     * @return OfferLabelRelation
     */
    private function createEntity(
        LabelEventHelper $labelEventHelper,
        AbstractLabelEvent $labelEvent
    ) {
        $entity = null;

        $uuid = $labelEventHelper->getUuid($labelEvent);
        $relationType = $labelEventHelper->getRelationType($labelEvent);
        $relationId = $labelEventHelper->getRelationId($labelEvent);

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
