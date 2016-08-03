<?php

namespace CultuurNet\UDB3\Label\ReadModels;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
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
            $this->applyLabelAdded($domainMessage->getPayload(), $domainMessage->getMetadata());
        } else if (is_a($event, AbstractLabelDeleted::class)) {
            $this->applyLabelDeleted($domainMessage->getPayload(), $domainMessage->getMetadata());
        } else {
            $this->handleSpecific($domainMessage);
        }
    }

    /**
     * @param AbstractLabelAdded $labelAdded
     * @param Metadata $metadata
     */
    abstract public function applyLabelAdded(AbstractLabelAdded $labelAdded, Metadata $metadata);

    /**
     * @param AbstractLabelDeleted $labelDeleted
     * @param Metadata $metadata
     */
    abstract public function applyLabelDeleted(AbstractLabelDeleted $labelDeleted, Metadata $metadata);
}
