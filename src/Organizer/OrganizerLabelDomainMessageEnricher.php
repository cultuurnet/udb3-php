<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\Label\AbstractLabelDomainMessageEnricher;
use CultuurNet\UDB3\Organizer\Events\AbstractLabelEvent;
use CultuurNet\UDB3\Organizer\Events\LabelAdded;
use CultuurNet\UDB3\Organizer\Events\LabelRemoved;

class OrganizerLabelDomainMessageEnricher extends AbstractLabelDomainMessageEnricher
{
    /**
     * @inheritdoc
     */
    public function supports(DomainMessage $domainMessage)
    {
        return ($domainMessage->getPayload() instanceof LabelAdded ||
            $domainMessage->getPayload() instanceof LabelRemoved);
    }

    /**
     * @inheritdoc
     */
    public function getLabelUuid(DomainMessage $domainMessage)
    {
        /** @var AbstractLabelEvent $labelEvent */
        $labelEvent = $domainMessage->getPayload();
        return $labelEvent->getLabelId();
    }
}
