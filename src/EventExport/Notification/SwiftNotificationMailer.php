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
     * @var SwiftNotificationMailFactory
     */
    private $messageFactory;

    /**
     * @param \Swift_Mailer $mailer
     * @param SwiftNotificationMailFactoryInterface $mailFactory
     */
    public function __construct(
        \Swift_Mailer $mailer,
        SwiftNotificationMailFactory $mailFactory
    ) {
        $this->mailer = $mailer;

    }

    public function sendNotificationMail(
        $address,
        EventExportResult $eventExportResult
    ) {
        $message = $this->messageFactory->createMessageFor($address, $eventExportResult);

        $sent = $this->mailer->send($message);

        print 'sent ' . $sent . ' e-mails' . PHP_EOL;
    }

}
