<?php

namespace CultuurNet\UDB3\EventBus;

use Broadway\Domain\DomainMessage;

interface DomainMessageEnricherInterface
{
    /**
     * @param DomainMessage $domainMessage
     * @return bool
     */
    public function supports(DomainMessage $domainMessage);

    /**
     * @param DomainMessage $domainMessage
     * @return DomainMessage
     */
    public function enrich(DomainMessage $domainMessage);
}
