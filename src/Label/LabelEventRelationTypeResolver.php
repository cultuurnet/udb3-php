<?php

namespace CultuurNet\UDB3\Label;

use CultuurNet\UDB3\Label\Specifications\LabelEventIsOfEventType;
use CultuurNet\UDB3\Label\Specifications\LabelEventIsOfOrganizerType;
use CultuurNet\UDB3\Label\Specifications\LabelEventIsOfPlaceType;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent as EventAbstractLabelEvent;
use CultuurNet\UDB3\Organizer\Events\AbstractLabelEvent as OrganizerAbstractLabelEvent;

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
     * @param EventAbstractLabelEvent|OrganizerAbstractLabelEvent $labelEvent
     * @return RelationType
     * @throws \InvalidArgumentException
     */
    public function getRelationType($labelEvent)
    {
        if ($this->eventTypeSpecification->isSatisfiedBy($labelEvent)) {
            $relationType = RelationType::EVENT();
        } else if ($this->placeTypeSpecification->isSatisfiedBy($labelEvent)) {
            $relationType = RelationType::PLACE();
        } else if ($this->organizerTypeSpecification->isSatisfiedBy($labelEvent)) {
            $relationType = RelationType::ORGANIZER();
        } else {
            $message = $this->createIllegalArgumentMessage($labelEvent);
            throw new \InvalidArgumentException($message);
        }

        return $relationType;
    }

    /**
     * @param EventAbstractLabelEvent|OrganizerAbstractLabelEvent $labelEvent
     * @return string
     */
    private function createIllegalArgumentMessage($labelEvent)
    {
        return 'Event with type ' . get_class($labelEvent) . ' can not be converted to a relation type!';
    }
}
