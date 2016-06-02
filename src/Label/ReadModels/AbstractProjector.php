<?php

namespace CultuurNet\UDB3\Label\ReadModels;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelDeleted;

abstract class AbstractProjector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait {
        DelegateEventHandlingToSpecificMethodTrait::handle as handleSpecific;
    }

    /**
     * @param DomainMessage $domainMessage
     */
    public function handle(DomainMessage $domainMessage)
    {
        $event = $domainMessage->getPayload();

        if (is_a($event, AbstractLabelAdded::class)) {
            $this->applyLabelAdded($domainMessage->getPayload());
        } else if (is_a($event, AbstractLabelDeleted::class)) {
            $this->applyLabelDeleted($domainMessage->getPayload());
        } else {
            $this->handleSpecific($domainMessage);
        }
    }

    /**
     * @param AbstractLabelAdded $labelAdded
     */
    abstract public function applyLabelAdded(AbstractLabelAdded $labelAdded);

    /**
     * @param AbstractLabelDeleted $labelDeleted
     */
    abstract public function applyLabelDeleted(AbstractLabelDeleted $labelDeleted);
}
