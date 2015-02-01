<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Notification;


use CultuurNet\UDB3\EventExport\EventExportResult;

class DefaultHTMLNotificationMailFormatter implements NotificationMailFormatterInterface
{
    public function getNotificationMailBody(
        EventExportResult $eventExportResult
    ) {
        $url = $eventExportResult->getUrl();
        return  '<a href="' . $url . '">' . $url . '</a>';
    }

}
