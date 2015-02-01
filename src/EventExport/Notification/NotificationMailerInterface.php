<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Notification;


use CultuurNet\UDB3\EventExport\EventExportResult;

interface NotificationMailerInterface
{
    public function sendNotificationMail($address, EventExportResult $eventExportResult);
}
