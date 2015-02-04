<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Notification;


use CultuurNet\UDB3\EventExport\EventExportResult;

class DefaultPlainTextBodyFactory implements BodyFactoryInterface
{
    public function getBodyFor(
        EventExportResult $eventExportResult
    ) {
        // Only put the URL in the mail, for now.
        return $eventExportResult->getUrl();
    }

}
