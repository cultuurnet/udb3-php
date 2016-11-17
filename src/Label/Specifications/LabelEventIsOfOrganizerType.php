<?php

namespace CultuurNet\UDB3\Label\Specifications;

use CultuurNet\UDB3\Organizer\Events\AbstractLabelEvent;
use CultuurNet\UDB3\Organizer\Events\LabelAdded;
use CultuurNet\UDB3\Organizer\Events\LabelRemoved;

class LabelEventIsOfOrganizerType implements LabelEventSpecificationInterface
{
    /**
     * @param AbstractLabelEvent $labelEvent
     * @return bool
     */
    public function isSatisfiedBy($labelEvent)
    {
        return ($labelEvent instanceof LabelAdded || $labelEvent instanceof LabelRemoved);
    }
}
