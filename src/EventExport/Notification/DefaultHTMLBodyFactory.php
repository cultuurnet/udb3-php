<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Notification;


use CultuurNet\UDB3\EventExport\EventExportResult;

class DefaultHTMLBodyFactory implements BodyFactoryInterface
{
    public function getBodyFor(
        EventExportResult $eventExportResult
    ) {
        $url = $eventExportResult->getUrl();
        return  '<a href="' . $url . '">' . $url . '</a>';
    }

}
