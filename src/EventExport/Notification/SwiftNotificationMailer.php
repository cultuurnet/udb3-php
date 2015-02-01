<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Notification;


use CultuurNet\UDB3\EventExport\EventExportResult;

class SwiftNotificationMailer implements NotificationMailerInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

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
     * @param \Swift_Mailer $mailer
     * @param NotificationMailFormatterInterface $plainTextMailFormatter
     * @param NotificationMailFormatterInterface $htmlMailFormatter
     */
    public function __construct(
        \Swift_Mailer $mailer,
        NotificationMailFormatterInterface $plainTextMailFormatter,
        NotificationMailFormatterInterface $htmlMailFormatter,
        NotificationMailSubjectFormatterInterface $subjectFormatter,
        $senderAddress,
        $senderName
    ) {
        $this->mailer = $mailer;
        $this->plainTextMailFormatter = $plainTextMailFormatter;
        $this->htmlMailFormatter = $htmlMailFormatter;
        $this->senderAddress = $senderAddress;
        $this->senderName = $senderName;
        $this->subjectFormatter = $subjectFormatter;
    }

    public function sendNotificationMail(
        $address,
        EventExportResult $eventExportResult
    ) {
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

        $sent = $this->mailer->send($message);

        print 'sent ' . $sent . ' e-mails' . PHP_EOL;
    }

}
