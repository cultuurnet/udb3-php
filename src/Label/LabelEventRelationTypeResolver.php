<?php

namespace CultuurNet\UDB3\Label;

use CultuurNet\UDB3\Label\Specifications\LabelEventIsOfEventType;
use CultuurNet\UDB3\Label\Specifications\LabelEventIsOfOrganizerType;
use CultuurNet\UDB3\Label\Specifications\LabelEventIsOfPlaceType;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use CultuurNet\UDB3\LabelEventInterface;

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

    /**
     * @var LabelEventIsOfOrganizerType
     */
    private $organizerTypeSpecification;

    public function __construct()
    {
        $this->eventTypeSpecification = new LabelEventIsOfEventType();
        $this->placeTypeSpecification = new LabelEventIsOfPlaceType();
        $this->organizerTypeSpecification = new LabelEventIsOfOrganizerType();
    }

    /**
     * @param LabelEventInterface $labelEvent
     * @return RelationType
     * @throws \InvalidArgumentException
     */
    public function getRelationType(LabelEventInterface $labelEvent)
    {
        if ($this->eventTypeSpecification->isSatisfiedBy($labelEvent)) {
            $relationType = RelationType::EVENT();
        } elseif ($this->placeTypeSpecification->isSatisfiedBy($labelEvent)) {
            $relationType = RelationType::PLACE();
        } elseif ($this->organizerTypeSpecification->isSatisfiedBy($labelEvent)) {
            $relationType = RelationType::ORGANIZER();
        } else {
            $message = $this->createIllegalArgumentMessage($labelEvent);
            throw new \InvalidArgumentException($message);
        }

        return $relationType;
    }

    /**
     * @param LabelEventInterface $labelEvent
     * @return string
     */
    private function createIllegalArgumentMessage($labelEvent)
    {
        return 'Event with type ' . get_class($labelEvent) . ' can not be converted to a relation type!';
    }
}
