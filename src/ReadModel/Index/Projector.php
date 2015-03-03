<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Organizer\ReadModel\Index\Projector.
 */

namespace CultuurNet\UDB3\ReadModel\Index;

use Broadway\Domain\DomainMessageInterface;
use CultuurNet\UDB3\Cdb\EventItemFactory;
use CultuurNet\UDB3\Event\EventCreated;
use CultuurNet\UDB3\Event\EventImportedFromUDB2;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Place\PlaceCreated;
use CultuurNet\UDB3\ReadModel\Udb3Projector;

/**
 * Logs new events / updates to an index for querying.
 */
class Projector extends Udb3Projector
{

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    /**
     *
     * @param PlaceImportedFromUDB2 $place
     */
    protected function applyPlaceImportedFromUDB2(PlaceImportedFromUDB2 $place)
    {

        $placeId = $place->getPlaceId();
        $userId = ''; // imported = no uid.
        $name = '';
        $postalCode = '';

        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $actorImportedFromUDB2->getCdbXmlNamespaceUri(),
            $actorImportedFromUDB2->getCdbXml()
        );

        $details = $place->getDetails();
        foreach ($details as $languageDetail) {
            $language = $languageDetail->getLanguage();

            // The first language detail found will be used to retrieve
            // properties from which in UDB3 are not any longer considered
            // to be language specific.
            if (!$detail) {
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

        $this->updateIndex($placeId, 'place', $userId, $name, $postalCode);
    }

    /**
     * Listener for event created commands.
     */
    protected function applyEventCreated(EventCreated $eventCreated, DomainMessageInterface $domainMessage)
    {

        $eventId = $eventCreated->getEventId();

        $metaData = $domainMessage->getMetadata()->serialize();
        $userId = isset($metaData['user_id']) ? $metaData['user_id'] : '';

        $location = $eventCreated->getLocation();
        $this->updateIndex($eventId, 'event', $userId, $eventCreated->getTitle(), $location->getPostalcode());
    }

    /**
     * Listener for place created commands.
     */
    protected function applyPlaceCreated(PlaceCreated $placeCreated, DomainMessageInterface $domainMessage)
    {

        $placeId = $placeCreated->getPlaceId();

        $metaData = $domainMessage->getMetadata()->serialize();
        $userId = isset($metaData['user_id']) ? $metaData['user_id'] : '';

        $address = $placeCreated->getAddress();
        $this->updateIndex($placeId, 'place', $userId, $placeCreated->getTitle(), $address->getPostalcode());
    }

    /**
     * Listener for organizer created commands.
     */
    protected function applyOrganizerCreated(OrganizerCreated $organizer, DomainMessageInterface $domainMessage)
    {

        $organizerId = $organizer->getOrganizerId();

        $metaData = $domainMessage->getMetadata()->serialize();
        $userId = isset($metaData['user_id']) ? $metaData['user_id'] : '';

        $addresses = $organizer->getAddresses();
        if (isset($addresses[0])) {
            $this->updateIndex($organizerId, 'organizer', $userId, $organizer->getTitle(), $addresses[0]->getPostalCode());
        }
    }

    /**
     * Update the index
     */
    protected function updateIndex($id, $type, $userId, $name, $postalCode)
    {
        $this->repository->updateIndex($id, $type, $userId, $name, $postalCode);
    }
}
