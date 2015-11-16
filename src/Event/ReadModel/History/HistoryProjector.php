<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\History;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\DescriptionTranslated;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventWasLabelled;
use CultuurNet\UDB3\Event\Events\TranslationApplied;
use CultuurNet\UDB3\Event\Events\LabelsMerged;
use CultuurNet\UDB3\Event\Events\Unlabelled;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\TitleTranslated;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\ReadModel\JsonDocument;
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
        DomainMessage $domainMessage
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

    private function applyEventCreatedFromCdbXml(
        EventCreatedFromCdbXml $eventCreatedFromCdbXml,
        DomainMessage $domainMessage
    ) {
        $consumerName = $this->getConsumerFromMetadata($domainMessage->getMetadata());

        $this->writeHistory(
            $eventCreatedFromCdbXml->getEventId()->toNative(),
            new Log(
                $this->domainMessageDateToNativeDate(
                    $domainMessage->getRecordedOn()
                ),
                new String(
                    'Aangemaakt via EntryAPI door consumer "' . $consumerName . '"'
                ),
                $this->getAuthorFromMetadata($domainMessage->getMetadata())
            )
        );
    }

    private function applyEventUpdatedFromCdbXml(
        EventUpdatedFromCdbXml $eventUpdatedFromCdbXml,
        DomainMessage $domainMessage
    ) {
        $consumerName = $this->getConsumerFromMetadata($domainMessage->getMetadata());

        $this->writeHistory(
            $eventUpdatedFromCdbXml->getEventId()->toNative(),
            new Log(
                $this->domainMessageDateToNativeDate(
                    $domainMessage->getRecordedOn()
                ),
                new String(
                    'Geüpdatet via EntryAPI door consumer "' . $consumerName . '"'
                ),
                $this->getAuthorFromMetadata($domainMessage->getMetadata())
            )
        );
    }

    /**
     * @param DateTime $date
     * @return \DateTime
     */
    private function domainMessageDateToNativeDate(DateTime $date)
    {
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
        DomainMessage $domainMessage
    ) {
        $this->writeHistory(
            $eventUpdatedFromUDB2->getEventId(),
            new Log(
                $this->domainMessageDateToNativeDate($domainMessage->getRecordedOn()),
                new String('Updatet vanuit UDB2')
            )
        );
    }

    /**
     * @param Metadata $metadata
     * @return String|null
     */
    private function getAuthorFromMetadata(Metadata $metadata)
    {
        $properties = $metadata->serialize();

        if (isset($properties['user_nick'])) {
            return new String($properties['user_nick']);
        }
    }

    /**
     * @param Metadata $metadata
     * @return String|null
     */
    private function getConsumerFromMetadata(Metadata $metadata)
    {
        $properties = $metadata->serialize();

        if (isset($properties['consumer']['name'])) {
            return new String($properties['consumer']['name']);
        }
    }

    private function applyEventWasLabelled(
        EventWasLabelled $eventWasLabelled,
        DomainMessage $domainMessage
    ) {
        $this->writeHistory(
            $eventWasLabelled->getEventId(),
            new Log(
                $this->domainMessageDateToNativeDate($domainMessage->getRecordedOn()),
                new String("Label '{$eventWasLabelled->getLabel()}' toegepast"),
                $this->getAuthorFromMetadata($domainMessage->getMetadata())
            )
        );
    }

    private function applyUnlabelled(
        Unlabelled $unlabelled,
        DomainMessage $domainMessage
    ) {
        $this->writeHistory(
            $unlabelled->getEventId(),
            new Log(
                $this->domainMessageDateToNativeDate($domainMessage->getRecordedOn()),
                new String("Label '{$unlabelled->getLabel()}' verwijderd"),
                $this->getAuthorFromMetadata($domainMessage->getMetadata())
            )
        );
    }

    /**
     * @param LabelsMerged $labelsMerged
     * @param DomainMessage $domainMessage
     */
    private function applyLabelsMerged(
        LabelsMerged $labelsMerged,
        DomainMessage $domainMessage
    ) {
        $labels = $labelsMerged->getLabels()->toStrings();
        // Quote labels.
        $quotedLabels = array_map(
            function ($label) {
                return "'{$label}'";
            },
            $labels
        );
        $quotedLabelsString = implode(', ', $quotedLabels);

        $message = "Labels {$quotedLabelsString} toegepast";

        $consumerName = $this->getConsumerFromMetadata($domainMessage->getMetadata());

        if ($consumerName) {
            $message .= ' via EntryAPI door consumer "' . $consumerName . '"';
        }

        $this->writeHistory(
            $labelsMerged->getEventId()->toNative(),
            new Log(
                $this->domainMessageDateToNativeDate($domainMessage->getRecordedOn()),
                new String($message),
                $this->getAuthorFromMetadata($domainMessage->getMetadata())
            )
        );
    }

    private function applyTitleTranslated(
        TitleTranslated $titleTranslated,
        DomainMessage $domainMessage
    ) {
        $this->writeHistory(
            $titleTranslated->getEventId(),
            new Log(
                $this->domainMessageDateToNativeDate($domainMessage->getRecordedOn()),
                new String("Titel vertaald ({$titleTranslated->getLanguage()})"),
                $this->getAuthorFromMetadata($domainMessage->getMetadata())
            )
        );
    }

    private function applyDescriptionTranslated(
        DescriptionTranslated $descriptionTranslated,
        DomainMessage $domainMessage
    ) {
        $this->writeHistory(
            $descriptionTranslated->getEventId(),
            new Log(
                $this->domainMessageDateToNativeDate(
                    $domainMessage->getRecordedOn()
                ),
                new String("Beschrijving vertaald ({$descriptionTranslated->getLanguage()})"),
                $this->getAuthorFromMetadata($domainMessage->getMetadata())
            )
        );
    }

    private function applyTranslationApplied(
        TranslationApplied $translationApplied,
        DomainMessage $domainMessage
    ) {
        $fields = [];

        if ($translationApplied->getTitle() !== null) {
            $fields[] = 'titel';
        }
        if ($translationApplied->getShortDescription() !== null) {
            $fields[] = 'korte beschrijving';
        }
        if ($translationApplied->getLongDescription() !== null) {
            $fields[] = 'lange beschrijving';
        }
        $fieldString = ucfirst(implode(', ', $fields));

        $logMessage = "{$fieldString} vertaald ({$translationApplied->getLanguage()->getCode()})";

        $consumerName = $this->getConsumerFromMetadata($domainMessage->getMetadata());
        if ($consumerName) {
            $logMessage .= " via EntryAPI door consumer \"{$consumerName}\"";
        }

        $this->writeHistory(
            $translationApplied->getEventId()->toNative(),
            new Log(
                $this->domainMessageDateToNativeDate(
                    $domainMessage->getRecordedOn()
                ),
                new String($logMessage),
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
