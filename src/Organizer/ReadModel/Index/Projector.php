<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Organizer\ReadModel\Index\Projector.
 */

namespace CultuurNet\UDB3\Organizer\ReadModel\Index;

use CultuurNet\UDB3\Actor\ActorImportedFromUDB2;
use CultuurNet\UDB3\ReadModel\Udb3Projector;

/**
 * Logs new organizers to an index for querying.
 */
class Projector extends Udb3Projector {

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    protected function applyActorImportedFromUDB2(ActorImportedFromUDB2 $organizer)
    {

        $organizerId = $organizer->getActorId();
        $userId = ''; // imported = no uid.

        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $organizer->getCdbXmlNamespaceUri(),
            $organizer->getCdbXml()
        );

        $details = $udb2Actor->getDetails();
        foreach ($details as $languageDetail) {
          // The first language detail found will be used to retrieve
          // properties from which in UDB3 are not any longer considered
          // to be language specific.
          if (!$detail) {
              $detail = $languageDetail;
          }
        }

        $name = $detail->getTitle();

        // Get the zip.
        $contact_cdb = $udb2Actor->getContactInfo();
        /** @var \CultureFeed_Cdb_Data_Address[] $addresses **/
        $addresses = $contact_cdb->getAddresses();

        foreach ($addresses as $address) {
            $address = $address->getPhysicalAddress();
            if ($address) {
              $zip = $address->getZip();
              break;
            }
        }

        $this->updateIndex($organizerId, $userId, $name, $zip);
    }

    /**
     * Listener for organizer created commands.
     */
    protected function applyOrganizerCreated(OrganizerCreated $organizer) {

        $organizerId = $organizer->getOrganizerId();
        $this->updateIndex($organizerId, $userId, $name, $zip);

    }

    /**
     * Update the index
     */
    protected function updateIndex($organizerId, $userId, $title, $zip)
    {
        $this->repository->updateIndex($organizerId, 'organizer', $userId, $title, $zip);
    }

}
