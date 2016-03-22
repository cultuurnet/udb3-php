<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Organizer\ReadModel\Index\Projector.
 */

namespace CultuurNet\UDB3\ReadModel\Index;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultureFeed_Cdb_Item_Event;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Cdb\CreatedByToUserIdResolverInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventProjectedToJSONLD;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2Event;
use CultuurNet\UDB3\Place\PlaceProjectedToJSONLD;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use ValueObjects\String\String as StringLiteral;

/**
 * Logs new events / updates to an index for querying.
 */
class Projector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait {
        DelegateEventHandlingToSpecificMethodTrait::handle as handleMethodSpecificEvents;
    }

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var CreatedByToUserIdResolverInterface
     */
    protected $userIdResolver;

    /**
     * A list of events that should trigger an index item update.
     *  The key is the namespaced class name.
     *  The value is the method the method to call to get the id of the index item.
     *
     * @var string[]
     */
    protected static $indexUpdateEvents = [
        EventProjectedToJSONLD::class => 'getEventId',
        PlaceProjectedToJSONLD::class => 'getId'
    ];

    /**
     * @param RepositoryInterface $repository
     */
    public function __construct(
        RepositoryInterface $repository,
        CreatedByToUserIdResolverInterface $createdByToUserIdResolver
    ) {
        $this->repository = $repository;
        $this->userIdResolver = $createdByToUserIdResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(DomainMessage $domainMessage)
    {
        $this->handleIndexUpdateEvents($domainMessage);
        $this->handleMethodSpecificEvents($domainMessage);
    }

    protected function handleIndexUpdateEvents(DomainMessage $domainMessage)
    {
        $event = $domainMessage->getPayload();
        $eventName = get_class($event);

        if (array_key_exists($eventName, self::$indexUpdateEvents)) {
            $itemIdentifier = self::$indexUpdateEvents[$eventName];

            if ($itemIdentifier) {
                $this->setItemUpdateDate(
                    $event->{$itemIdentifier}(),
                    new DateTime($domainMessage->getRecordedOn()->toString())
                );
            }
        }
    }

    /**
     * @param string $itemId
     * @param DateTimeInterface $dateUpdated
     */
    protected function setItemUpdateDate($itemId, DateTimeInterface $dateUpdated)
    {
        $this->repository->setUpdateDate($itemId, $dateUpdated);
    }
    
    /**
     * @param \CultureFeed_Cdb_Item_Base $udb2Item
     *
     * @return null|string
     */
    protected function resolveUserId(\CultureFeed_Cdb_Item_Base $udb2Item)
    {
        $createdByIdentifier = $udb2Item->getCreatedBy();
        if ($createdByIdentifier) {
            $userId = $this->userIdResolver->resolveCreatedByToUserId(
                new StringLiteral($createdByIdentifier)
            );
        }

        return isset($userId) ? (string) $userId : '';
    }

    /**
     *
     * @param EventImportedFromUDB2 $eventImportedFromUDB2
     */
    protected function applyEventImportedFromUDB2(
        EventImportedFromUDB2 $eventImportedFromUDB2
    ) {
        $eventId = $eventImportedFromUDB2->getEventId();
        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml()
        );
        $userId = $this->resolveUserId($udb2Event);

        $this->updateIndexWithUDB2Event($eventId, EntityType::EVENT(), $userId, $udb2Event);
    }

    /**
     * @param PlaceImportedFromUDB2Event $placeImportedFromUDB2Event
     */
    protected function applyPlaceImportedFromUDB2Event(
        PlaceImportedFromUDB2Event $placeImportedFromUDB2Event
    ) {
        $placeId = $placeImportedFromUDB2Event->getActorId();
        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $placeImportedFromUDB2Event->getCdbXmlNamespaceUri(),
            $placeImportedFromUDB2Event->getCdbXml()
        );
        $userId = $this->resolveUserId($udb2Event);

        $this->updateIndexWithUDB2Event($placeId, EntityType::PLACE(), $userId, $udb2Event);
    }

    /**
     * @param string $itemId
     * @param EntityType $itemType
     * @param string $userId
     * @param CultureFeed_Cdb_Item_Event $udb2Event
     */
    protected function updateIndexWithUDB2Event(
        $itemId,
        EntityType $itemType,
        $userId,
        CultureFeed_Cdb_Item_Event $udb2Event
    ) {
        /** @var \CultureFeed_Cdb_Data_EventDetail $detail */
        $detail = null;
        $postalCode = '';

        $details = $udb2Event->getDetails();
        foreach ($details as $languageDetail) {
            // The first language detail found will be used.
            $detail = $languageDetail;
            break;
        }

        $name = trim($detail->getTitle());

        // Ignore items without a name. They might occur in UDB2 although this
        // is not considered normal.
        if (empty($name)) {
            return;
        }

        $contact_cdb = $udb2Event->getContactInfo();
        if ($contact_cdb) {
            $addresses = $contact_cdb->getAddresses();

            /** @var \CultureFeed_Cdb_Data_Address $address */
            foreach ($addresses as $address) {
                /** @var \CultureFeed_Cdb_Data_Address_PhysicalAddress $physicalAddress */
                $physicalAddress = $address->getPhysicalAddress();
                if ($physicalAddress) {
                    $postalCode = $physicalAddress->getZip();
                }
            }
        }

        $creationDate = $this->dateTimeFromUDB2DateString(
            $udb2Event->getCreationDate()
        );

        $this->updateIndex($itemId, $itemType, (string) $userId, $name, $postalCode, $creationDate);
    }

    /**
     *
     * @param PlaceImportedFromUDB2 $placeImportedFromUDB2
     */
    protected function applyPlaceImportedFromUDB2(PlaceImportedFromUDB2 $placeImportedFromUDB2)
    {

        $placeId = $placeImportedFromUDB2->getActorId();
        /** @var \CultureFeed_Cdb_Data_ActorDetail $detail */
        $detail = null;
        $postalCode = '';

        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $placeImportedFromUDB2->getCdbXmlNamespaceUri(),
            $placeImportedFromUDB2->getCdbXml()
        );

        $userId = $this->resolveUserId($udb2Actor);

        $details = $udb2Actor->getDetails();
        foreach ($details as $languageDetail) {
            // The first language detail found will be used.
            $detail = $languageDetail;
            break;
        }

        $name = trim($detail->getTitle());

        // Ignore items without a name. They might occur in UDB2 although this
        // is not considered normal.
        if (empty($name)) {
            return;
        }

        $contact_cdb = $udb2Actor->getContactInfo();
        if ($contact_cdb) {
            $addresses = $contact_cdb->getAddresses();

            /** @var \CultureFeed_Cdb_Data_Address $address */
            foreach ($addresses as $address) {
                $physicalAddress = $address->getPhysicalAddress();
                if ($physicalAddress) {
                    $postalCode = $physicalAddress->getZip();
                }
            }
        }

        $creationDate = $this->dateTimeFromUDB2DateString(
            $udb2Actor->getCreationDate()
        );

        $this->updateIndex(
            $placeId,
            EntityType::PLACE(),
            $userId,
            $name,
            $postalCode,
            $creationDate
        );
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

        $this->updateIndex(
            $eventId,
            EntityType::EVENT(),
            $userId,
            $eventCreated->getTitle(),
            $location->getPostalcode(),
            $creationDate
        );
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
        $this->updateIndex(
            $placeId,
            EntityType::PLACE(),
            $userId,
            $placeCreated->getTitle(),
            $address->getPostalcode(),
            $creationDate
        );
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
            $this->updateIndex(
                $organizerId,
                EntityType::ORGANIZER(),
                $userId,
                $organizer->getTitle(),
                $addresses[0]->getPostalCode(),
                $creationDate
            );
        }
    }

    protected function applyOrganizerImportedFromUDB2(OrganizerImportedFromUDB2 $organizerImportedFromUDB2)
    {

        $organizerId = $organizerImportedFromUDB2->getActorId();
        /** @var \CultureFeed_Cdb_Data_ActorDetail $detail */
        $detail = null;
        $postalCode = '';

        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $organizerImportedFromUDB2->getCdbXmlNamespaceUri(),
            $organizerImportedFromUDB2->getCdbXml()
        );

        $userId = $this->resolveUserId($udb2Actor);

        $details = $udb2Actor->getDetails();
        foreach ($details as $languageDetail) {
            // The first language detail found will be used.
            $detail = $languageDetail;
            break;
        }

        $name = trim($detail->getTitle());

        // Ignore items without a name. They might occur in UDB2 although this
        // is not considered normal.
        if (empty($name)) {
            return;
        }

        $creationDate = $this->dateTimeFromUDB2DateString(
            $udb2Actor->getCreationDate()
        );

        $this->updateIndex(
            $organizerId,
            EntityType::ORGANIZER(),
            $userId,
            $name,
            $postalCode,
            $creationDate
        );
    }

    /**
     * @param $dateString
     *  A UDB2 formatted date string
     *
     * @return DateTimeInterface
     */
    protected function dateTimeFromUDB2DateString($dateString)
    {
        return DateTime::createFromFormat(
            'Y-m-d?H:i:s',
            $dateString,
            new DateTimeZone('Europe/Brussels')
        );
    }

    /**
     * Update the index
     */
    protected function updateIndex(
        $id,
        EntityType $type,
        $userId,
        $name,
        $postalCode,
        DateTimeInterface $creationDate = null
    ) {
        $this->repository->updateIndex(
            $id,
            $type,
            $userId,
            $name,
            $postalCode,
            $creationDate
        );
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
