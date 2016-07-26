<?php

namespace CultuurNet\UDB3\Label;

use CultuurNet\UDB3\Label\Specifications\OfferLabelEventIsOfEventType;
use CultuurNet\UDB3\Label\Specifications\OfferLabelEventIsOfPlaceType;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;
use CultuurNet\UDB3\Offer\OfferType;

class LabelEventOfferTypeResolver implements LabelEventOfferTypeResolverInterface
{
    /**
     * @var OfferLabelEventIsOfEventType
     */
    private $eventTypeSpecification;

    /**
     * @var OfferLabelEventIsOfPlaceType
     */
    private $placeTypeSpecification;

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
