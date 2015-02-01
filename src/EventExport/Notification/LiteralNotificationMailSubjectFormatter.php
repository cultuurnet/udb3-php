<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Notification;


use CultuurNet\UDB3\EventExport\EventExportResult;

class LiteralNotificationMailSubjectFormatter implements NotificationMailSubjectFormatterInterface
{
    /**
     * @var string
     */
    private $subject;

    /**
     * @param string $subject
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    public function getSubject(EventExportResult $eventExportResult)
    {
        return $this->subject;
    }

}
