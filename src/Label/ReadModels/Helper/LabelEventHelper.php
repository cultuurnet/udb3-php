<?php

namespace CultuurNet\UDB3\Label\ReadModels\Helper;

use CultuurNet\UDB3\Event\Events\LabelAdded as EventLabelAdded;
use CultuurNet\UDB3\Event\Events\LabelDeleted as EventLabelDeleted;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Place\Events\LabelAdded as PlaceLabelAdded;
use CultuurNet\UDB3\Place\Events\LabelDeleted as PlaceLabelDeleted;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class LabelEventHelper
{
    /**
     * @var ReadRepositoryInterface
     */
    private $readRepository;

    /**
     * Helper constructor.
     * @param ReadRepositoryInterface $readRepository
     */
    public function __construct(ReadRepositoryInterface $readRepository)
    {
        $this->readRepository = $readRepository;
    }

    /**
     * @param AbstractLabelEvent $labelEvent
     * @return UUID|null
     */
    public function getUuid(AbstractLabelEvent $labelEvent)
    {
        $uuid = null;
        
        $name = new StringLiteral((string) $labelEvent->getLabel());

        $entity = $this->readRepository->getByName($name);
        if ($entity !== null) {
            $uuid = $entity->getUuid();
        }
        
        return $uuid;
    }

    /**
     * @param AbstractLabelEvent $labelEvent
     * @return OfferType
     * @throws \InvalidArgumentException
     */
    public function getRelationType(AbstractLabelEvent $labelEvent)
    {
        if ($this->isEventRelationType($labelEvent)) {
            $relationType = OfferType::EVENT();
        } else if ($this->isPlaceRelationType($labelEvent)) {
            $relationType = OfferType::PLACE();
        } else {
            $message = $this->createIllegalArgumentMessage($labelEvent);
            throw new \InvalidArgumentException($message);
        }

        return $relationType;
    }

    /**
     * @param AbstractLabelEvent $labelEvent
     * @return StringLiteral
     */
    public function getRelationId(AbstractLabelEvent $labelEvent)
    {
        return new StringLiteral($labelEvent->getItemId());
    }

    /**
     * @param AbstractLabelEvent $labelEvent
     * @return bool
     */
    private function isEventRelationType(AbstractLabelEvent $labelEvent)
    {
        return ($labelEvent instanceof EventLabelAdded ||
            $labelEvent instanceof EventLabelDeleted);
    }

    /**
     * @param AbstractLabelEvent $labelEvent
     * @return bool
     */
    private function isPlaceRelationType(AbstractLabelEvent $labelEvent)
    {
        return ($labelEvent instanceof PlaceLabelAdded ||
            $labelEvent instanceof PlaceLabelDeleted);
    }

    /**
     * @param AbstractLabelEvent $labelEvent
     * @return string
     */
    private function createIllegalArgumentMessage(AbstractLabelEvent $labelEvent)
    {
        return 'Event with type ' . get_class($labelEvent) . ' can not be converted to a relation type!';
    }
}
