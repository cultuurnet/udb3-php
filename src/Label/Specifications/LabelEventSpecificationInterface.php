<?php

namespace CultuurNet\UDB3\Label\Specifications;

use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent as EventAbstractLabelEvent;
use CultuurNet\UDB3\Organizer\Events\AbstractLabelEvent as OrganizerAbstractLabelEvent;

interface LabelEventSpecificationInterface
{
    /**
     * @param EventAbstractLabelEvent|OrganizerAbstractLabelEvent $labelEvent
     * @return bool
     */
    public function isSatisfiedBy($labelEvent);
}
