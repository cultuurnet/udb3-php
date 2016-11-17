<?php

namespace CultuurNet\UDB3\Label\Specifications;

use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;
use CultuurNet\UDB3\Place\Events\LabelAdded;
use CultuurNet\UDB3\Place\Events\LabelDeleted;

class LabelEventIsOfPlaceType implements LabelEventSpecificationInterface
{
    /**
     * @param AbstractLabelEvent $labelEvent
     * @return bool
     */
    public function isSatisfiedBy(AbstractLabelEvent $labelEvent)
    {
        return ($labelEvent instanceof LabelAdded || $labelEvent instanceof LabelDeleted);
    }
}
