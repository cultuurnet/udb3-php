<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Organizer\OrganizerLDProjector.
 */

namespace CultuurNet\UDB3\Organizer;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use CultuurNet\UDB3\Actor\ActorLDProjector;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\Actor\ActorImportedFromUDB2;
use CultuurNet\UDB3\Organizer\OrganizerProjectedToJSONLD;

class OrganizerLDProjector extends ActorLDProjector
{
    /**
     * @param ActorImportedFromUDB2 $actorImportedFromUDB2
     */
    public function applyOrganizerImportedFromUDB2(
        ActorImportedFromUDB2 $actorImportedFromUDB2
    ) {
        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $actorImportedFromUDB2->getCdbXmlNamespaceUri(),
            $actorImportedFromUDB2->getCdbXml()
        );

        $document = $this->newDocument($actorImportedFromUDB2->getActorId());
        $actorLd = $document->getBody();

        $detail = null;

        /** @var \CultureFeed_Cdb_Data_Detail[] $details */
        $details = $udb2Actor->getDetails();

        foreach ($details as $languageDetail) {
            // The first language detail found will be used to retrieve
            // properties from which in UDB3 are not any longer considered
            // to be language specific.
            if (!$detail) {
                $detail = $languageDetail;
            }
        }

        $actorLd->name = $detail->getTitle();

        $actorLd->addresses = array();
        $contact_cdb = $udb2Actor->getContactInfo();
        if ($contact_cdb) {
            /** @var \CultureFeed_Cdb_Data_Address[] $addresses * */
            $addresses = $contact_cdb->getAddresses();

            foreach ($addresses as $address) {
                $address = $address->getPhysicalAddress();

                if ($address) {
                    $actorLd->addresses[] = array(
                        'addressCountry' => $address->getCountry(),
                        'addressLocality' => $address->getCity(),
                        'postalCode' => $address->getZip(),
                        'streetAddress' =>
                            $address->getStreet() . ' ' .
                            $address->getHouseNumber(),
                    );
                }
            }

            $emails_cdb = $contact_cdb->getMails();
            if(count($emails_cdb) > 0) {
                $emails = array();
                foreach ($emails_cdb as $mail) {
                    $emails[] = $mail->getMailAddress();
                }
                $actorLd->email = $emails;
            }

            $phones_cdb = $contact_cdb->getPhones();
            if(count($phones_cdb) > 0) {
                $phones = array();
                foreach ($phones_cdb as $phone) {
                    $phones[] = $phone->getNumber();
                }
                $actorLd->phone = $phones;
            }

        }

        $actorLdModel = new JsonDocument(
            $actorImportedFromUDB2->getActorId()
        );

        $this->repository->save($actorLdModel->withBody($actorLd));

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
          new OrganizerProjectedToJSONLD($id)
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

        $organizerLd = $document->getBody();
        $organizerLd->{'@id'} = $this->iriGenerator->iri($id);

        // @todo provide Event-LD context here relative to the base URI
        $organizerLd->{'@context'} = '/api/1.0/organizer.jsonld';

        return $document->withBody($organizerLd);
    }
}
