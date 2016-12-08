<?php

namespace CultuurNet\UDB3\Event\ReadModel\History;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\Events\DescriptionTranslated;
use CultuurNet\UDB3\Event\Events\EventCreatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromCdbXml;
use CultuurNet\UDB3\Event\Events\EventUpdatedFromUDB2;
use CultuurNet\UDB3\Event\Events\LabelAdded;
use CultuurNet\UDB3\Event\Events\LabelRemoved;
use CultuurNet\UDB3\Event\Events\LabelsMerged;
use CultuurNet\UDB3\Event\Events\TitleTranslated;
use CultuurNet\UDB3\Event\Events\TranslationApplied;
use CultuurNet\UDB3\Event\Events\TranslationDeleted;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Offer\ReadModel\History\OfferHistoryProjector;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use ValueObjects\String\String;

class HistoryProjector extends OfferHistoryProjector implements EventListenerInterface
{

    protected function applyEventImportedFromUDB2(
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

    protected function applyEventCreatedFromCdbXml(
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

    protected function applyEventUpdatedFromCdbXml(
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

    protected function applyEventUpdatedFromUDB2(
        EventUpdatedFromUDB2 $eventUpdatedFromUDB2,
        DomainMessage $domainMessage
    ) {
        $this->writeHistory(
            $eventUpdatedFromUDB2->getEventId(),
            new Log(
                $this->domainMessageDateToNativeDate($domainMessage->getRecordedOn()),
                new String('Geüpdatet vanuit UDB2')
            )
        );
    }

    /**
     * @param LabelsMerged $labelsMerged
     * @param DomainMessage $domainMessage
     */
    protected function applyLabelsMerged(
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

    protected function applyTranslationApplied(
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
     * @param TranslationDeleted $translationDeleted
     * @param DomainMessage $domainMessage
     */
    protected function applyTranslationDeleted(
        TranslationDeleted $translationDeleted,
        DomainMessage $domainMessage
    ) {
        $message = "Vertaling verwijderd ({$translationDeleted->getLanguage()})";

        $consumerName = $this->getConsumerFromMetadata($domainMessage->getMetadata());

        if ($consumerName) {
            $message .= ' via EntryAPI door consumer "' . $consumerName . '"';
        }

        $this->writeHistory(
            $translationDeleted->getEventId()->toNative(),
            new Log(
                $this->domainMessageDateToNativeDate(
                    $domainMessage->getRecordedOn()
                ),
                new String($message),
                $this->getAuthorFromMetadata($domainMessage->getMetadata())
            )
        );
    }

    /**
     * @return string
     */
    protected function getLabelAddedClassName()
    {
        return LabelAdded::class;
    }

    /**
     * @return string
     */
    protected function getLabelRemovedClassName()
    {
        return LabelRemoved::class;
    }

    /**
     * @return string
     */
    protected function getTitleTranslatedClassName()
    {
        return TitleTranslated::class;
    }

    /**
     * @return string
     */
    protected function getDescriptionTranslatedClassName()
    {
        return DescriptionTranslated::class;
    }
}
