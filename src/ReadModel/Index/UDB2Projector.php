<?php

namespace CultuurNet\UDB3\ReadModel\Index;

use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Cdb\ItemBaseAdapterFactory;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\Events\EventImportedFromUDB2;
use CultuurNet\UDB3\Place\Events\PlaceImportedFromUDB2;
use ValueObjects\Web\Domain;

class UDB2Projector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var Domain
     */
    protected $UDB2Domain;

    /**
     * @var ItemBaseAdapterFactory
     */
    protected $itemBaseAdapterFactory;

    /**
     * @param RepositoryInterface $repository
     * @param ItemBaseAdapterFactory $itemBaseAdapterFactory
     * @param Domain $UDB2Domain
     */
    public function __construct(
        RepositoryInterface $repository,
        ItemBaseAdapterFactory $itemBaseAdapterFactory,
        Domain $UDB2Domain
    ) {
        $this->repository = $repository;
        $this->itemBaseAdapterFactory = $itemBaseAdapterFactory;
        $this->UDB2Domain = $UDB2Domain;
    }

    /**
     * @param EventImportedFromUDB2 $eventImportedFromUDB2
     */
    protected function applyEventImportedFromUDB2(
        EventImportedFromUDB2 $eventImportedFromUDB2
    ) {
        $itemId = $eventImportedFromUDB2->getEventId();
        $udb2Event = EventItemFactory::createEventFromCdbXml(
            $eventImportedFromUDB2->getCdbXmlNamespaceUri(),
            $eventImportedFromUDB2->getCdbXml()
        );

        $itemType = EntityType::EVENT();

        $udb2EventAdapter = $this->itemBaseAdapterFactory->create($udb2Event);

        $userId = $udb2EventAdapter->getResolvedCreatorUserId();

        /** @var \CultureFeed_Cdb_Data_EventDetail $detail */
        $detail = null;
        $postalCode = '';
        $city = '';
        $country = '';

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
                    $city = $physicalAddress->getCity();
                    $country = $physicalAddress->getCountry();
                }
            }
        }

        $creationDate = $udb2EventAdapter->getCreationDateTime();

        $this->repository->updateIndex(
            $itemId,
            $itemType,
            (string) $userId,
            $name,
            $postalCode,
            $city,
            $country,
            $this->UDB2Domain,
            $creationDate
        );
    }

    /**
     * @param PlaceImportedFromUDB2 $placeImportedFromUDB2
     */
    protected function applyPlaceImportedFromUDB2(PlaceImportedFromUDB2 $placeImportedFromUDB2)
    {
        $placeId = $placeImportedFromUDB2->getActorId();
        /** @var \CultureFeed_Cdb_Data_ActorDetail $detail */
        $detail = null;
        $postalCode = '';
        $city = '';
        $country = '';

        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $placeImportedFromUDB2->getCdbXmlNamespaceUri(),
            $placeImportedFromUDB2->getCdbXml()
        );

        $udb2ActorAdapter = $this->itemBaseAdapterFactory->create($udb2Actor);

        $userId = $udb2ActorAdapter->getResolvedCreatorUserId();

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
                    $city = $physicalAddress->getCity();
                    $country = $physicalAddress->getCountry();
                }
            }
        }

        $creationDate = $udb2ActorAdapter->getCreationDateTime();

        $this->repository->updateIndex(
            $placeId,
            EntityType::PLACE(),
            $userId,
            $name,
            $postalCode,
            $city,
            $country,
            $this->UDB2Domain,
            $creationDate
        );
    }
}
