<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations;

use CultuurNet\UDB3\Label\ReadModels\AbstractProjector;
use CultuurNet\UDB3\Label\ReadModels\Helper\LabelEventHelper;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Entity;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\WriteRepositoryInterface;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;

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

        $this->writeRepository->save(
            $entity->getUuid(),
            $entity->getRelationType(),
            $entity->getRelationId()
        );
    }

    /**
     * @inheritdoc
     */
    public function applyLabelDeleted(AbstractLabelDeleted $labelDeleted)
    {
        $entity = $this->createEntity($this->labelEventHelper, $labelDeleted);

        $this->writeRepository->deleteByUuid($entity->getUuid());
    }

    /**
     * @param LabelEventHelper $labelEventHelper
     * @param AbstractLabelEvent $labelEvent
     * @return Entity
     */
    private function createEntity(
        LabelEventHelper $labelEventHelper,
        AbstractLabelEvent $labelEvent
    ) {
        $uuid = $labelEventHelper->getUuid($labelEvent);
        $relationType = $labelEventHelper->getRelationType($labelEvent);
        $relationId = $labelEventHelper->getRelationId($labelEvent);

        return new Entity(
            $uuid,
            $relationType,
            $relationId
        );
    }
}
