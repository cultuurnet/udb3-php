<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Notification;


use CultuurNet\UDB3\EventExport\EventExportResult;

class DefaultPlainTextNotificationMailFormatter implements NotificationMailFormatterInterface
{
    public function getNotificationMailBody(
        EventExportResult $eventExportResult
    ) {
        // Only put the URL in the mail, for now.
        return $eventExportResult->getUrl();
    }

}
