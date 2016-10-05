<?php

namespace CultuurNet\UDB3\Label;

use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\Label\Events\AbstractEvent;
use CultuurNet\UDB3\Label\Events\MadeInvisible;
use CultuurNet\UDB3\Label\Events\MadeVisible;

class LabelDomainMessageEnricher extends AbstractLabelDomainMessageEnricher
{
    /**
     * @inheritdoc
     */
    public function supports(DomainMessage $domainMessage)
    {
        return ($domainMessage->getPayload() instanceof MadeVisible ||
            $domainMessage->getPayload() instanceof MadeInvisible);
    }

    /**
     * @inheritdoc
     */
    public function getLabelUuid(DomainMessage $domainMessage)
    {
        /** @var AbstractEvent $abstractEvent */
        $abstractEvent = $domainMessage->getPayload();

        return $abstractEvent->getUuid();
    }
}
