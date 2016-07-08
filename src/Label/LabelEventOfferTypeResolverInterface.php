<?php

namespace CultuurNet\UDB3\Label;

use CultuurNet\UDB3\Offer\Events\AbstractLabelEvent;
use CultuurNet\UDB3\Offer\OfferType;

interface LabelEventOfferTypeResolverInterface
{
    /**
     * @param AbstractLabelEvent $labelEvent
     * @return OfferType
     * @throws \InvalidArgumentException
     */
    public function getOfferType(AbstractLabelEvent $labelEvent);
}
