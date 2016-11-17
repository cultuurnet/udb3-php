<?php

namespace CultuurNet\UDB3\Label;

use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent as OfferAbstractLabelEvent;
use CultuurNet\UDB3\Organizer\Events\AbstractLabelEvent as OrganizerAbstractLabelEvent;

interface LabelEventRelationTypeResolverInterface
{
    /**
     * @param OfferAbstractLabelEvent|OrganizerAbstractLabelEvent $labelEvent
     * @return RelationType
     * @throws \InvalidArgumentException
     */
    public function getRelationType($labelEvent);
}
