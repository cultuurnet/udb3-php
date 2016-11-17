<?php

namespace CultuurNet\UDB3\Label;

use CultuurNet\UDB3\Label\Specifications\LabelEventIsOfEventType;
use CultuurNet\UDB3\Label\Specifications\LabelEventIsOfPlaceType;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;

class LabelEventRelationTypeResolver implements LabelEventRelationTypeResolverInterface
{
    /**
     * @var LabelEventIsOfEventType
     */
    private $eventTypeSpecification;

    /**
     * @var LabelEventIsOfPlaceType
     */
    private $placeTypeSpecification;

    public function __construct()
    {
        $this->eventTypeSpecification = new LabelEventIsOfEventType();
        $this->placeTypeSpecification = new LabelEventIsOfPlaceType();
    }

    /**
     * @param AbstractLabelEvent $labelEvent
     * @return RelationType
     * @throws \InvalidArgumentException
     */
    public function getRelationType($labelEvent)
    {
        if ($this->eventTypeSpecification->isSatisfiedBy($labelEvent)) {
            $offerType = RelationType::EVENT();
        } else if ($this->placeTypeSpecification->isSatisfiedBy($labelEvent)) {
            $offerType = RelationType::PLACE();
        } else {
            $message = $this->createIllegalArgumentMessage($labelEvent);
            throw new \InvalidArgumentException($message);
        }

        return $offerType;
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
