<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;


class EventImportedFromUDB2 extends EventEvent
{
    protected $cdbXml;

    public function __construct($eventId, $cdbXml)
    {
        parent::__construct($eventId);
        $this->cdbXml = $cdbXml;
    }

    public function getCdbXml()
    {
        return $this->cdbXml;
    }
}
