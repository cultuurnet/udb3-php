<?php
/**
 * @file
 */
namespace CultuurNet\UDB3\EventExport\Notification;

use CultuurNet\UDB3\EventExport\EventExportResult;

interface SwiftNotificationMailFactoryInterface
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
