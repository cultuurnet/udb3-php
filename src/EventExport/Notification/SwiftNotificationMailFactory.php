<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Notification;


use CultuurNet\UDB3\EventExport\EventExportResult;

class SwiftNotificationMailFactory implements SwiftNotificationMailFactoryInterface
{
    /**
     * @var NotificationMailFormatterInterface
     */
    private $plainTextMailFormatter;

    /**
     * @var NotificationMailFormatterInterface
     */
    private $htmlMailFormatter;

    /**
     * @var NotificationMailSubjectFormatterInterface
     */
    private $subjectFormatter;

    /**
     * @var string
     */
    private $senderAddress;

    /**
     * @var string
     */
    private $senderName;

    /**
     * @param NotificationMailFormatterInterface $plainTextMailFormatter
     * @param NotificationMailFormatterInterface $htmlMailFormatter
     * @param NotificationMailSubjectFormatterInterface $subjectFormatter
     * @param string $senderAddress
     * @param string $senderName
     */
    public function __construct(
        NotificationMailFormatterInterface $plainTextMailFormatter,
        NotificationMailFormatterInterface $htmlMailFormatter,
        NotificationMailSubjectFormatterInterface $subjectFormatter,
        $senderAddress,
        $senderName
    )
    {
        $this->plainTextMailFormatter = $plainTextMailFormatter;
        $this->htmlMailFormatter = $htmlMailFormatter;
        $this->senderAddress = $senderAddress;
        $this->senderName = $senderName;
        $this->subjectFormatter = $subjectFormatter;
    }

    /**
     * @param string $address
     * @return \Swift_Message
     */
    public function createMessageFor($address, EventExportResult $eventExportResult)
    {
        $message = new \Swift_Message($this->subjectFormatter->getSubject($eventExportResult));
        $message->setBody(
            $this->htmlMailFormatter->getNotificationMailBody(
                $eventExportResult
            ),
            'text/html'
        );
        $message->addPart(
            $this->plainTextMailFormatter->getNotificationMailBody(
                $eventExportResult
            ),
            'text/plain'
        );

        $message->addTo($address);

        $message->setSender($this->senderAddress, $this->senderName);
        $message->setFrom($this->senderAddress, $this->senderName);

        return $message;
    }
}
