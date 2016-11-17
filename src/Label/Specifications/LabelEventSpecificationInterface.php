<?php

namespace CultuurNet\UDB3\Label\Specifications;

use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;

interface LabelEventSpecificationInterface
{
    /**
     * @param AbstractLabelEvent $labelEvent
     * @return bool
     */
    public function isSatisfiedBy(AbstractLabelEvent $labelEvent);
}
