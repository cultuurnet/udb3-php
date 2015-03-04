<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\History;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessageInterface;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\DescriptionTranslated;
use CultuurNet\UDB3\Event\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\EventWasTagged;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\Event\TagErased;
use CultuurNet\UDB3\Event\TitleTranslated;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use ValueObjects\String\String;

class HistoryProjector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var DocumentRepositoryInterface
     */
    private $documentRepository;

    public function __construct(DocumentRepositoryInterface $documentRepository)
    {
        $this->documentRepository = $documentRepository;
    }

    private function applyEventImportedFromUDB2(
        EventImportedFromUDB2 $eventImportedFromUDB2,
        DomainMessageInterface $domainMessage
    ) {
        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml()
        );

        $this->writeHistory(
            $eventImportedFromUDB2->getEventId(),
            new Log(
                $this->dateFromUdb2DateString(
                    $udb2Event->getCreationDate()
                ),
                new String('Aangemaakt in UDB2'),
                new String($udb2Event->getCreatedBy())
            )
        );

        $this->writeHistory(
            $eventImportedFromUDB2->getEventId(),
            new Log(
                $this->domainMessageDateToNativeDate(
                    $domainMessage->getRecordedOn()
                ),
                new String('Geïmporteerd vanuit UDB2')
            )
        );
    }

    private function domainMessageDateToNativeDate(DateTime $date) {
        $dateString = $date->toString();
        return \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $dateString
        );
    }

    /**
     * @param $dateString
     * @return \DateTime
     */
    private function dateFromUdb2DateString($dateString)
    {
        return \DateTime::createFromFormat(
            'Y-m-d?H:i:s',
            $dateString,
            new \DateTimeZone('Europe/Brussels')
        );
    }

    private function applyEventUpdatedFromUDB2(
        EventUpdatedFromUDB2 $eventUpdatedFromUDB2,
        DomainMessageInterface $domainMessage
    ) {
        $this->writeHistory(
            $eventUpdatedFromUDB2->getEventId(),
            new Log(
                $this->domainMessageDateToNativeDate($domainMessage->getRecordedOn()),
                new String('Updatet vanuit UDB2')
            )
        );
    }

    private function getAuthorFromMetadata(Metadata $metadata)
    {
        $properties = $metadata->serialize();

        return new String($properties['uitid_nick']);
    }

    private function applyEventWasTagged(
        EventWasTagged $eventWasTagged,
        DomainMessageInterface $domainMessage
    ) {
        $this->writeHistory(
            $eventWasTagged->getEventId(),
            new Log(
                $domainMessage->getRecordedOn(),
                new String("Label '{$eventWasTagged->getKeyword()}' toegepast"),
                $this->getAuthorFromMetadata($domainMessage->getMetadata())
            )
        );
    }

    private function applyTagErased(
        TagErased $tagErased,
        DomainMessageInterface $domainMessage
    ) {
        $this->writeHistory(
            $tagErased->getEventId(),
            new Log(
                $domainMessage->getRecordedOn(),
                new String("Label '{$tagErased->getKeyword()}' verwijderd"),
                $this->getAuthorFromMetadata($domainMessage->getMetadata())
            )
        );
    }

    private function applyTitleTranslated(
        TitleTranslated $titleTranslated,
        DomainMessageInterface $domainMessage
    ) {
        $this->writeHistory(
            $titleTranslated->getEventId(),
            new Log(
                $domainMessage->getRecordedOn(),
                new String("Titel vertaald in taal {$titleTranslated->getLanguage()}"),
                $this->getAuthorFromMetadata($domainMessage->getMetadata())
            )
        );
    }

    private function applyDescriptionTranslated(
        DescriptionTranslated $descriptionTranslated,
        DomainMessageInterface $domainMessage
    ) {
        $this->writeHistory(
            $descriptionTranslated->getEventId(),
            new Log(
                $domainMessage->getRecordedOn(),
                new String("Beschrijving vertaald in taal {$descriptionTranslated->getLanguage()}"),
                $this->getAuthorFromMetadata($domainMessage->getMetadata())
            )
        );
    }

    /**
     * @param string $eventId
     * @return JsonDocument
     */
    private function loadDocumentFromRepositoryByEventId($eventId)
    {
        $historyDocument = $this->documentRepository->get($eventId);

        if (!$historyDocument) {
            $historyDocument = new JsonDocument($eventId, '[]');
        }

        return $historyDocument;
    }

    /**
     * @param string $eventId
     * @param Log[]|Log $logs
     */
    protected function writeHistory($eventId, $logs)
    {
        $historyDocument = $this->loadDocumentFromRepositoryByEventId($eventId);

        $history = $historyDocument->getBody();

        if (!is_array($logs)) {
            $logs = [$logs];
        }

        // Append most recent one to the top.
        foreach ($logs as $log) {
            array_unshift($history, $log);
        }

        $this->documentRepository->save(
            $historyDocument->withBody($history)
        );
    }
}
