<?php

namespace CultuurNet\UDB3\ReadModel\Index;

use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\DomainMessageAdapter as DomainMessage;
use CultuurNet\UDB3\Event\Events\EventCopied;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\EventDeleted;
use CultuurNet\UDB3\Event\Events\EventProjectedToJSONLD;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTraitWithDomainMessageAdapter;
use CultuurNet\UDB3\Offer\Events\AbstractEventWithIri;
use CultuurNet\UDB3\Offer\IriOfferIdentifierFactoryInterface;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Place\Events\PlaceDeleted;
use CultuurNet\UDB3\Place\Events\PlaceProjectedToJSONLD;
use ValueObjects\Web\Domain;
use ValueObjects\Web\Url;

/**
 * Logs new events / updates to an index for querying.
 */
class Projector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTraitWithDomainMessageAdapter;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var Domain
     */
    protected $localDomain;

    /**
     * @var IriOfferIdentifierFactoryInterface
     */
    protected $identifierFactory;

    /**
     * @param RepositoryInterface $repository
     * @param Domain $localDomain
     * @param IriOfferIdentifierFactoryInterface $identifierFactory
     */
    public function __construct(
        RepositoryInterface $repository,
        Domain $localDomain,
        IriOfferIdentifierFactoryInterface $identifierFactory
    ) {
        $this->repository = $repository;
        $this->localDomain = $localDomain;
        $this->identifierFactory = $identifierFactory;
    }

    public function applyEventProjectedToJSONLD(
        EventProjectedToJSONLD $eventProjectedToJSONLD,
        DomainMessage $domainMessage
    ) {
        $this->setItemUpdateDate(
            $eventProjectedToJSONLD,
            $domainMessage
        );
    }

    public function applyPlaceProjectedToJSONLD(
        PlaceProjectedToJSONLD $placeProjectedToJSONLD,
        DomainMessage $domainMessage
    ) {
        $this->setItemUpdateDate(
            $placeProjectedToJSONLD,
            $domainMessage
        );
    }

    /**
     * @param AbstractEventWithIri $eventWithIri
     * @param DomainMessage $domainMessage
     */
    protected function setItemUpdateDate(
        AbstractEventWithIri $eventWithIri,
        DomainMessage $domainMessage
    ) {
        $identifier = $this->identifierFactory->fromIri(
            Url::fromNative($eventWithIri->getIri())
        );

        $this->repository->setUpdateDate(
            $identifier->getId(),
            $domainMessage->getRecordedDateTime()
        );
    }

    /**
     * Listener for event created commands.
     * @param EventCreated $eventCreated
     * @param DomainMessage $domainMessage
     */
    protected function applyEventCreated(
        EventCreated $eventCreated,
        DomainMessage $domainMessage
    ) {
        $eventId = $eventCreated->getEventId();

        $location = $eventCreated->getLocation();

        $this->addNewItemToIndex(
            $domainMessage,
            $eventId,
            EntityType::EVENT(),
            $eventCreated->getTitle(),
            $location->getAddress()->getPostalCode(),
            $location->getAddress()->getCountry()->getCode()
        );
    }

    /**
     * @param EventCopied $eventCopied
     * @param DomainMessage $domainMessage
     */
    public function applyEventCopied(
        EventCopied $eventCopied,
        DomainMessage $domainMessage
    ) {
        $eventId = $eventCopied->getItemId();

        $this->addNewItemToIndex(
            $domainMessage,
            $eventId,
            EntityType::EVENT()
        );
    }

    /**
     * Listener for place created commands.
     * @param PlaceCreated $placeCreated
     * @param DomainMessage $domainMessage
     */
    protected function applyPlaceCreated(
        PlaceCreated $placeCreated,
        DomainMessage $domainMessage
    ) {
        $placeId = $placeCreated->getPlaceId();

        $address = $placeCreated->getAddress();

        $this->addNewItemToIndex(
            $domainMessage,
            $placeId,
            EntityType::PLACE(),
            $placeCreated->getTitle(),
            $address->getPostalCode(),
            $address->getCountry()->getCode()
        );
    }

    private function addNewItemToIndex(
        DomainMessage $domainMessage,
        $id,
        EntityType $entityType,
        $name = '',
        $postalCode = '',
        $country = ''
    ) {
        $this->repository->updateIndex(
            $id,
            $entityType,
            $domainMessage->getUserId(),
            $name,
            $postalCode,
            $country,
            $this->localDomain,
            $domainMessage->getRecordedDateTime()
        );
    }

    /**
     * Remove the index for events
     * @param EventDeleted $eventDeleted
     */
    public function applyEventDeleted(
        EventDeleted $eventDeleted
    ) {
        $this->repository->deleteIndex(
            $eventDeleted->getItemId(),
            EntityType::EVENT()
        );
    }

    /**
     * Remove the index for places
     * @param PlaceDeleted $placeDeleted
     */
    public function applyPlaceDeleted(
        PlaceDeleted $placeDeleted
    ) {
        $this->repository->deleteIndex(
            $placeDeleted->getItemId(),
            EntityType::PLACE()
        );
    }
}
