<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Notification;

use CultuurNet\UDB3\EventExport\EventExportResult;

/**
 * Interface NotificationMailFormatterInterface
 * @package CultuurNet\UDB3\EventExport\Notification
 *
 * Implementations of NotificationMailFormatterInterface are responsible for generating
 * the message body of a notification e-mail.
 */
interface NotificationMailFormatterInterface {

    public function getNotificationMailBody(EventExportResult $eventExportResult);
}
