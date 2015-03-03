<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Place\PlaceLDProjector.
 */

namespace CultuurNet\UDB3\Place;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\DomainMessageInterface;
use Broadway\Domain\Metadata;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use CultuurNet\UDB3\Actor\ActorImportedFromUDB2;
use CultuurNet\UDB3\Actor\ActorLDProjector;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\Place\ReadModel\JSONLD\CdbXMLImporter;
use CultuurNet\UDB3\Place\TypicalAgeRangeUpdated;

class PlaceLDProjector extends ActorLDProjector
{
    /**
     * @param ActorImportedFromUDB2 $actorImportedFromUDB2
     */
    public function applyPlaceImportedFromUDB2(
        ActorImportedFromUDB2 $actorImportedFromUDB2
    ) {
        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $actorImportedFromUDB2->getCdbXmlNamespaceUri(),
            $actorImportedFromUDB2->getCdbXml()
        );

        $document = $this->newDocument($actorImportedFromUDB2->getActorId());
        $actorLd = $document->getBody();

        $cdbXMLImporter = new CdbXMLImporter();
        $actorLd = $cdbXMLImporter->documentWithCdbXML(
            $actorLd,
            $udb2Actor
        );

        $this->repository->save($document->withBody($actorLd));

        $this->publishJSONLDUpdated(
            $actorImportedFromUDB2->getActorId()
        );
    }

    protected function publishJSONLDUpdated($id)
    {
        $generator = new Version4Generator();
        $events[] = DomainMessage::recordNow(
            $generator->generate(),
            1,
            new Metadata(),
            new PlaceProjectedToJSONLD($id)
        );

        $this->eventBus->publish(
            new DomainEventStream($events)
        );
    }

    /**
     * @param string $id
     * @return JsonDocument
     */
    protected function newDocument($id)
    {
        $document = new JsonDocument($id);

        $placeLd = $document->getBody();
        $placeLd->{'@id'} = $this->iriGenerator->iri($id);

        // @todo provide Event-LD context here relative to the base URI
        $placeLd->{'@context'} = '/api/1.0/place.jsonld';

        return $document->withBody($placeLd);
    }

    /**
     * @param PlaceCreated $placeCreated
     */
    protected function applyPlaceCreated(PlaceCreated $placeCreated, DomainMessageInterface $domainMessage)
    {
        $document = $this->newDocument($placeCreated->getPlaceId());

        $jsonLD = $document->getBody();

        $jsonLD->{'@id'} = $this->iriGenerator->iri(
            $placeCreated->getPlaceId()
        );
        $jsonLD->name = $placeCreated->getTitle();

        $address = $placeCreated->getAddress();
        $jsonLD->address = array(
            'addressCountry' => $address->getCountry(),
            'addressLocality' => $address->getLocality(),
            'postalCode' => $address->getPostalCode(),
            'streetAddress' => $address->getStreetAddress(),
        );

        $calendar = $placeCreated->getCalendar();
        if (!empty($calendar)) {
            $startDate = $calendar->getStartDate();
            $endDate = $calendar->getEndDate();

            // All calendar types allow startDate (and endDate).
            // One timestamp - full day.
            // One timestamp - start hour.
            // One timestamp - start and end hour.
            if (!empty($startDate)) {
                $jsonLD->startDate = $startDate;
            }
            if (!empty($endDate)) {
                $jsonLD->endDate = $endDate;
            }

            // Timestamps should be subEvents in jsonLD.
            if ($calendar->getType() == 'timestamps') {
                $jsonLD->subEvent = array();
                foreach ($calendar->getTimestamps() as $timestamp) {
                    $startDate = $timestamp->getDate();
                    if ($timestamp->showStartHour()) {
                        $startDate .= $timestamp->getTimestart();
                    }
                    $endDate = $timestamp->getDate();
                    if ($timestamp->showEndHour()) {
                        $endDate .= $timestamp->getTimeend();
                    }

                    $jsonLD->subEvent[] = array(
                      '@type' => 'Event',
                      'startDate' => $startDate,
                      'endDate' => $endDate,
                    );
                }
            }
        }

        // Period.
        // Period with openingtimes.
        // Permanent - "altijd open".
        // Permanent - with openingtimes.
        $openingHours = $calendar->getOpeningHours();
        if (!empty($openingHours)) {
            $jsonLD->openingHours = array();
            foreach ($calendar->getOpeningHours() as $openingHour) {
                $schedule = array('dayOfWeek' => $openingHour->daysOfWeek);
                if (!empty($openingHour->opens)) {
                    $schedule['opens'] = $openingHour->opens;
                }
                if (!empty($openingHour->closes)) {
                    $schedule['closes'] = $openingHour->closes;
                }
                $jsonLD->openingHours[] = $schedule;
            }
        }

        $eventType = $placeCreated->getEventType();
        $jsonLD->terms = array(
          array(
            'label' => $eventType->getLabel(),
            'domain' => $eventType->getDomain(),
            'id' => $eventType->getId()
          )
        );

        $theme = $placeCreated->getTheme();
        if (!empty($theme)) {
            $jsonLD->terms[] = [
              'label' => $theme->getLabel(),
              'domain' => $theme->getDomain(),
              'id' => $theme->getId()
            ];
        }

        $recordedOn = $domainMessage->getRecordedOn()->toString();
        $jsonLD->created = \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $recordedOn
        )->format('c');

        $metaData = $domainMessage->getMetadata()->serialize();
        if (isset($metaData['user_id']) && isset($metaData['user_nick'])) {
            $jsonLD->creator = "{$metaData['user_id']} ({$metaData['user_nick']})";
        }

        $this->repository->save($document->withBody($jsonLD));
    }

    /**
     * Apply the description updated event to the place repository.
     * @param DescriptionUpdated $descriptionUpdated
     */
    protected function applyDescriptionUpdated(
        DescriptionUpdated $descriptionUpdated
    ) {

        $document = $this->loadPlaceDocumentFromRepository($descriptionUpdated);

        $placeLD = $document->getBody();
        $placeLD->description->{'nl'} = $descriptionUpdated->getDescription();

        $this->repository->save($document->withBody($placeLD));
    }

    /**
     * Apply the typical age range updated event to the event repository.
     * @param TypicalAgeRangeUpdated $typicalAgeRangeUpdated
     */
    protected function applyTypicalAgeRangeUpdated(
        TypicalAgeRangeUpdated $typicalAgeRangeUpdated
    ) {
        $document = $this->loadPlaceDocumentFromRepository($typicalAgeRangeUpdated);

        $eventLd = $document->getBody();

        if ($typicalAgeRangeUpdated->getTypicalAgeRange() === "-1") {
            unset($eventLd->typicalAgeRange);
        } else {
            $eventLd->typicalAgeRange = $typicalAgeRangeUpdated->getTypicalAgeRange();
        }

        $this->repository->save($document->withBody($eventLd));
    }

    /**
     * @param PlaceEvent $place
     * @return JsonDocument
     */
    protected function loadPlaceDocumentFromRepository(PlaceEvent $place)
    {
        $document = $this->repository->get($place->getPlaceId());

        if (!$document) {
            return $this->newDocument($place->getPlaceId());
        }

        return $document;
    }
}
