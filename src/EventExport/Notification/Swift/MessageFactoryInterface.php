<?php
/**
 * @file
 */
namespace CultuurNet\UDB3\EventExport\Notification\Swift;

use CultuurNet\UDB3\EventExport\EventExportResult;

interface MessageFactoryInterface
{
    /**
     * @param string $address
     * @return \Swift_Message
     */
    public function createMessageFor(
        $address,
        EventExportResult $eventExportResult
    );
}
