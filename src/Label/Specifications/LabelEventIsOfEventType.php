<?php

namespace CultuurNet\UDB3\Label\Specifications;

use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Event\Events\LabelDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;

class LabelEventIsOfEventType implements LabelEventSpecificationInterface
{
    /**
     * @param AbstractLabelEvent $labelEvent
     * @return bool
     */
    public function isSatisfiedBy($labelEvent)
    {
        return ($labelEvent instanceof LabelAdded || $labelEvent instanceof LabelDeleted);
    }
}
