<?php

namespace CultuurNet\UDB3\Label\ReadModels;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\LabelEventInterface;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded as OfferAbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelRemoved as OfferAbstractLabelRemoved;
use CultuurNet\UDB3\Organizer\Events\LabelAdded as OrganizerLabelAdded;
use CultuurNet\UDB3\Organizer\Events\LabelRemoved as OrganizerLabelRemoved;

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
        $payload = $domainMessage->getPayload();

        if ($this->isLabelAdded($payload)) {
            $this->applyLabelAdded(
                $domainMessage->getPayload(),
                $domainMessage->getMetadata()
            );
        } else if ($this->isLabelRemoved($payload)) {
            $this->applyLabelRemoved(
                $domainMessage->getPayload(),
                $domainMessage->getMetadata()
            );
        } else {
            $this->handleSpecific($domainMessage);
        }
    }

    /**
     * @param LabelEventInterface $labelAdded
     * @param Metadata $metadata
     */
    abstract public function applyLabelAdded(LabelEventInterface $labelAdded, Metadata $metadata);

    /**
     * @param LabelEventInterface $labelRemoved
     * @param Metadata $metadata
     */
    abstract public function applyLabelRemoved(LabelEventInterface $labelRemoved, Metadata $metadata);

    /**
     * @param $payload
     * @return bool
     */
    private function isLabelAdded($payload)
    {
        return ($payload instanceof OfferAbstractLabelAdded ||
            $payload instanceof OrganizerLabelAdded);
    }

    /**
     * @param $payload
     * @return bool
     */
    private function isLabelRemoved($payload)
    {
        return ($payload instanceof OfferAbstractLabelRemoved ||
            $payload instanceof OrganizerLabelRemoved);
    }
}
