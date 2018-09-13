<?php

namespace CultuurNet\UDB3\EventHandling;

use Broadway\Domain\DomainMessage;
use CultuurNet\UDB3\DomainMessageAdapter;

trait DelegateEventHandlingToSpecificMethodTraitWithDomainMessageAdapter
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * {@inheritDoc}
     */
    public function handle(DomainMessage $domainMessage)
    {
        $event  = $domainMessage->getPayload();
        $method = $this->getHandleMethodName($event);

        if ($method) {
            $this->$method($event, new DomainMessageAdapter($domainMessage));
        }
    }
}
