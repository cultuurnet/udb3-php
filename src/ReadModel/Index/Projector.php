<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Organizer\ReadModel\Index\Projector.
 */

namespace CultuurNet\UDB3\ReadModel\Index;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use DateTime;
use DateTimeZone;

/**
 * Logs new events / updates to an index for querying.
 */
class Projector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     *
     * @param EventImportedFromUDB2 $eventImportedFromUDB2
     */
    protected function applyEventImportedFromUDB2(EventImportedFromUDB2 $eventImportedFromUDB2)
    {

        $eventId = $eventImportedFromUDB2->getEventId();
        $userId = ''; // imported = no uid.
        $name = '';
        $postalCode = '';

        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml()
        );

        $details = $udb2Event->getDetails();
        foreach ($details as $languageDetail) {
            $language = $languageDetail->getLanguage();

            // The first language detail found will be used to retrieve
            // properties from which in UDB3 are not any longer considered
            // to be language specific.
            if (!isset($detail)) {
                $detail = $languageDetail;
            }

            $name = $languageDetail->getTitle();
        }

        $contact_cdb = $udb2Event->getContactInfo();
        if ($contact_cdb) {
            $addresses = $contact_cdb->getAddresses();

            foreach ($addresses as $address) {
                $address = $address->getPhysicalAddress();
                if ($address) {
                    $postalCode = $address->getZip();
                }
            }
        }

        $dateString = $udb2Event->getCreationDate();
        $creationDate = DateTime::createFromFormat(
            'Y-m-d?H:i:s',
            $dateString,
            new DateTimeZone('Europe/Brussels')
        );

        $this->updateIndex($eventId, EntityType::EVENT(), $userId, $name, $postalCode, $creationDate);
    }

    /**
     *
     * @param PlaceImportedFromUDB2 $placeImportedFromUDB2
     */
    protected function applyPlaceImportedFromUDB2(PlaceImportedFromUDB2 $placeImportedFromUDB2)
    {

        $placeId = $placeImportedFromUDB2->getActorId();
        $userId = ''; // imported = no uid.
        $name = '';
        $postalCode = '';

        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $placeImportedFromUDB2->getCdbXmlNamespaceUri(),
            $placeImportedFromUDB2->getCdbXml()
        );

        $details = $udb2Actor->getDetails();
        foreach ($details as $languageDetail) {
            $language = $languageDetail->getLanguage();

            // The first language detail found will be used to retrieve
            // properties from which in UDB3 are not any longer considered
            // to be language specific.
            if (!isset($detail)) {
                $detail = $languageDetail;
            }

            $name = $languageDetail->getTitle();
        }

        $contact_cdb = $udb2Actor->getContactInfo();
        if ($contact_cdb) {
            $addresses = $contact_cdb->getAddresses();

            foreach ($addresses as $address) {
                $address = $address->getPhysicalAddress();
                if ($address) {
                    $postalCode = $address->getZip();
                }
            }
        }

        $dateString = $udb2Actor->getCreationDate();
        $creationDate = DateTime::createFromFormat(
            'Y-m-d?H:i:s',
            $dateString,
            new DateTimeZone('Europe/Brussels')
        );

        $this->updateIndex($placeId, EntityTYpe::PLACE(), $userId, $name, $postalCode, $creationDate);
    }

    /**
     * Listener for event created commands.
     */
    protected function applyEventCreated(EventCreated $eventCreated, DomainMessage $domainMessage)
    {

        $eventId = $eventCreated->getEventId();

        $metaData = $domainMessage->getMetadata()->serialize();
        $userId = isset($metaData['user_id']) ? $metaData['user_id'] : '';

        $location = $eventCreated->getLocation();

        $creationDate = new DateTime('now', new DateTimeZone('Europe/Brussels'));

        $this->updateIndex($eventId, EntityType::EVENT(), $userId, $eventCreated->getTitle(), $location->getPostalcode(), $creationDate);
    }

    /**
     * Listener for place created commands.
     */
    protected function applyPlaceCreated(PlaceCreated $placeCreated, DomainMessage $domainMessage)
    {

        $placeId = $placeCreated->getPlaceId();

        $metaData = $domainMessage->getMetadata()->serialize();
        $userId = isset($metaData['user_id']) ? $metaData['user_id'] : '';

        $address = $placeCreated->getAddress();

        $creationDate = new DateTime('now', new DateTimeZone('Europe/Brussels'));
        $this->updateIndex($placeId, EntityType::PLACE(), $userId, $placeCreated->getTitle(), $address->getPostalcode(), $creationDate);
    }

    /**
     * Listener for organizer created commands.
     */
    protected function applyOrganizerCreated(OrganizerCreated $organizer, DomainMessage $domainMessage)
    {

        $organizerId = $organizer->getOrganizerId();

        $metaData = $domainMessage->getMetadata()->serialize();
        $userId = isset($metaData['user_id']) ? $metaData['user_id'] : '';

        $addresses = $organizer->getAddresses();
        if (isset($addresses[0])) {
            $creationDate = new DateTime('now', new DateTimeZone('Europe/Brussels'));
            $this->updateIndex($organizerId, EntityType::ORGANIZER(), $userId, $organizer->getTitle(), $addresses[0]->getPostalCode(), $creationDate);
        }
    }

    protected function applyOrganizerImportedFromUDB2(OrganizerImportedFromUDB2 $organizerImportedFromUDB2)
    {

        $organizerId = $organizerImportedFromUDB2->getActorId();
        $userId = ''; // imported = no uid.
        $name = '';
        $postalCode = '';

        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $organizerImportedFromUDB2->getCdbXmlNamespaceUri(),
            $organizerImportedFromUDB2->getCdbXml()
        );

        $details = $udb2Actor->getDetails();
        /** @var \CultureFeed_Cdb_Data_ActorDetail $languageDetail */
        foreach ($details as $languageDetail) {
            $language = $languageDetail->getLanguage();

            // The first language detail found will be used to retrieve
            // properties from which in UDB3 are not any longer considered
            // to be language specific.
            if (!isset($detail)) {
                $detail = $languageDetail;
            }

            $name = $languageDetail->getTitle();
        }

        $dateString = $udb2Actor->getCreationDate();
        $creationDate = DateTime::createFromFormat(
            'Y-m-d?H:i:s',
            $dateString,
            new DateTimeZone('Europe/Brussels')
        );

        $this->updateIndex($organizerId, EntityTYpe::ORGANIZER(), $userId, $name, $postalCode, $creationDate);
    }

    /**
     * Update the index
     */
    protected function updateIndex($id, EntityType $type, $userId, $name, $postalCode, \DateTimeInterface $creationDate = null)
    {
        $this->repository->updateIndex($id, $type, $userId, $name, $postalCode, $creationDate);
    }

    /**
     * Remove the index for events
     */
    public function applyEventDeleted(EventDeleted $eventDeleted, DomainMessage $domainMessage)
    {
        $this->repository->deleteIndex($eventDeleted->getEventId(), EntityType::EVENT());
    }

    /**
     * Remove the index for places
     */
    public function applyPlaceDeleted(PlaceDeleted $placeDeleted, DomainMessage $domainMessage)
    {
        $this->repository->deleteIndex($placeDeleted->getPlaceId(), EntityType::PLACE());
    }
}
