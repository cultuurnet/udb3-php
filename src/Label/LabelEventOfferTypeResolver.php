<?php

namespace CultuurNet\UDB3\Label;

use CultuurNet\UDB3\Event\Events\LabelAdded as EventLabelAdded;
use CultuurNet\UDB3\Event\Events\LabelDeleted as EventLabelDeleted;
use CultuurNet\UDB3\Label\Specifications\OfferLabelEventIsOfEventType;
use CultuurNet\UDB3\Label\Specifications\OfferLabelEventIsOfPlaceType;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Place\Events\LabelAdded as PlaceLabelAdded;
use CultuurNet\UDB3\Place\Events\LabelDeleted as PlaceLabelDeleted;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class LabelEventOfferTypeResolver implements LabelEventOfferTypeResolverInterface
{
    public function __construct()
    {
        $this->eventTypeSpecification = new OfferLabelEventIsOfEventType();
        $this->placeTypeSpecification = new OfferLabelEventIsOfPlaceType();
    }

    /**
     * @param AbstractLabelEvent $labelEvent
     * @return OfferType
     * @throws \InvalidArgumentException
     */
    public function getOfferType(AbstractLabelEvent $labelEvent)
    {
        if ($this->eventTypeSpecification->isSatisfiedBy($labelEvent)) {
            $relationType = OfferType::EVENT();
        } else if ($this->placeTypeSpecification->isSatisfiedBy($labelEvent)) {
            $relationType = OfferType::PLACE();
        } else {
            $message = $this->createIllegalArgumentMessage($labelEvent);
            throw new \InvalidArgumentException($message);
        }

        return $relationType;
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
