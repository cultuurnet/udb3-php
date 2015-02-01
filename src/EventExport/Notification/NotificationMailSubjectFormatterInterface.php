<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Notification;


use CultuurNet\UDB3\EventExport\EventExportResult;

interface NotificationMailSubjectFormatterInterface
{
    public function getSubject(EventExportResult $eventExportResult);
}
