<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Notification\Swift;


use CultuurNet\UDB3\EventExport\EventExportResult;
use CultuurNet\UDB3\EventExport\Notification\BodyFactoryInterface;
use CultuurNet\UDB3\EventExport\Notification\SubjectFactoryInterface;

class DefaultMessageFactory implements MessageFactoryInterface
{
    /**
     * @var BodyFactoryInterface
     */
    private $plainTextMailFormatter;

    /**
     * @var BodyFactoryInterface
     */
    private $htmlMailFormatter;

    /**
     * @var SubjectFactoryInterface
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
     * @param BodyFactoryInterface $plainTextMailFormatter
     * @param BodyFactoryInterface $htmlMailFormatter
     * @param SubjectFactoryInterface $subjectFormatter
     * @param string $senderAddress
     * @param string $senderName
     */
    public function __construct(
        BodyFactoryInterface $plainTextMailFormatter,
        BodyFactoryInterface $htmlMailFormatter,
        SubjectFactoryInterface $subjectFormatter,
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
            $this->htmlMailFormatter->getBodyFor(
                $eventExportResult
            ),
            'text/html'
        );
        $message->addPart(
            $this->plainTextMailFormatter->getBodyFor(
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
