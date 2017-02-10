<?php

namespace CultuurNet\UDB3\ReadModel\Index;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Cdb\CreatedByToUserIdResolverInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\Events\EventCopied;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventProjectedToJSONLD;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Offer\Events\AbstractEventWithIri;
use CultuurNet\UDB3\Offer\IriOfferIdentifierFactoryInterface;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceProjectedToJSONLD;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Domain;
use ValueObjects\Web\Url;

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
        EventProjectedToJSONLD::class,
        PlaceProjectedToJSONLD::class,
    ];

    /**
     * @var Domain
     */
    protected $localDomain;

    /**
     * @var Domain
     */
    protected $UDB2Domain;

    /**
     * @var IriOfferIdentifierFactoryInterface
     */
    protected $identifierFactory;

    /**
     * @param RepositoryInterface $repository
     * @param CreatedByToUserIdResolverInterface $createdByToUserIdResolver
     * @param Domain $localDomain
     * @param Domain $UDB2Domain
     * @param IriOfferIdentifierFactoryInterface $identifierFactory
     */
    public function __construct(
        RepositoryInterface $repository,
        CreatedByToUserIdResolverInterface $createdByToUserIdResolver,
        Domain $localDomain,
        Domain $UDB2Domain,
        IriOfferIdentifierFactoryInterface $identifierFactory
    ) {
        $this->repository = $repository;
        $this->userIdResolver = $createdByToUserIdResolver;
        $this->localDomain = $localDomain;
        $this->UDB2Domain = $UDB2Domain;
        $this->identifierFactory = $identifierFactory;
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
        /** @var AbstractEventWithIri $event */
        $event = $domainMessage->getPayload();
        $eventName = get_class($event);

        if (in_array($eventName, self::$indexUpdateEvents)) {
            $identifier = $this->identifierFactory->fromIri(
                Url::fromNative($event->getIri())
            );
            $dateUpdated = $this->getRecordedDate($domainMessage);

            $this->setItemUpdateDate($identifier->getId(), $dateUpdated);
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

        $creationDate = $this->dateTimeFromUDB2DateString(
            $udb2Event->getCreationDate()
        );

        $this->updateIndex(
            $eventId,
            EntityType::EVENT(),
            (string) $userId,
            $this->UDB2Domain,
            $creationDate
        );
    }

    /**
     *
     * @param PlaceImportedFromUDB2 $placeImportedFromUDB2
     */
    protected function applyPlaceImportedFromUDB2(PlaceImportedFromUDB2 $placeImportedFromUDB2)
    {
        $placeId = $placeImportedFromUDB2->getActorId();

        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $placeImportedFromUDB2->getCdbXmlNamespaceUri(),
            $placeImportedFromUDB2->getCdbXml()
        );

        $userId = $this->resolveUserId($udb2Actor);

        $creationDate = $this->dateTimeFromUDB2DateString(
            $udb2Actor->getCreationDate()
        );

        $this->updateIndex(
            $placeId,
            EntityType::PLACE(),
            $userId,
            $this->UDB2Domain,
            $creationDate
        );
    }

    /**
     * Listener for event created commands.
     * @param EventCreated $eventCreated
     * @param DomainMessage $domainMessage
     */
    protected function applyEventCreated(EventCreated $eventCreated, DomainMessage $domainMessage)
    {
        $eventId = $eventCreated->getEventId();

        $userId = $this->getUserId($domainMessage);

        $this->updateIndex(
            $eventId,
            EntityType::EVENT(),
            $userId,
            $this->localDomain,
            $this->getRecordedDate($domainMessage)
        );
    }

    /**
     * @param EventCopied $eventCopied
     * @param DomainMessage $domainMessage
     */
    public function applyEventCopied(EventCopied $eventCopied, DomainMessage $domainMessage)
    {
        $eventId = $eventCopied->getItemId();

        $userId = $this->getUserId($domainMessage);

        $this->updateIndex(
            $eventId,
            EntityType::EVENT(),
            $userId,
            $this->localDomain,
            $this->getRecordedDate($domainMessage)
        );
    }

    /**
     * Listener for place created commands.
     * @param PlaceCreated $placeCreated
     * @param DomainMessage $domainMessage
     */
    protected function applyPlaceCreated(PlaceCreated $placeCreated, DomainMessage $domainMessage)
    {
        $placeId = $placeCreated->getPlaceId();

        $userId = $this->getUserId($domainMessage);

        $this->updateIndex(
            $placeId,
            EntityType::PLACE(),
            $userId,
            $this->localDomain,
            $this->getRecordedDate($domainMessage)
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
     * @param $id
     * @param EntityType $type
     * @param $userId
     * @param Domain $owningDomain
     * @param DateTimeInterface $creationDate
     */
    protected function updateIndex(
        $id,
        EntityType $type,
        $userId,
        Domain $owningDomain,
        DateTimeInterface $creationDate = null
    ) {
        $this->repository->updateIndex(
            $id,
            $type,
            $userId,
            $owningDomain,
            $creationDate
        );
    }

    /**
     * Remove the index for events
     * @param EventDeleted $eventDeleted
     */
    public function applyEventDeleted(EventDeleted $eventDeleted)
    {
        $this->repository->deleteIndex($eventDeleted->getItemId(), EntityType::EVENT());
    }

    /**
     * Remove the index for places
     * @param PlaceDeleted $placeDeleted
     */
    public function applyPlaceDeleted(PlaceDeleted $placeDeleted)
    {
        $this->repository->deleteIndex($placeDeleted->getItemId(), EntityType::PLACE());
    }

    /**
     * @param DomainMessage $domainMessage
     * @return string
     */
    private function getUserId(DomainMessage $domainMessage)
    {
        $metaData = $domainMessage->getMetadata()->serialize();
        return isset($metaData['user_id']) ? $metaData['user_id'] : '';
    }

    /**
     * @param DomainMessage $domainMessage
     * @return DateTime
     */
    private function getRecordedDate(DomainMessage $domainMessage)
    {
        return new DateTime($domainMessage->getRecordedOn()->toString());
    }
}
