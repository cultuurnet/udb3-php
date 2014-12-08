<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Place\PlaceLDProjector.
 */

namespace CultuurNet\UDB3\Place;

use Broadway\ReadModel\Projector;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Event\EventImportedFromUDB2;

class PlaceLDProjector extends Projector
{
    /**
     * @var DocumentRepositoryInterface
     */
    protected $repository;

    /**
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * @param DocumentRepositoryInterface $repository
     * @param IriGeneratorInterface $iriGenerator
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator
    ) {
        $this->repository = $repository;
        $this->iriGenerator = $iriGenerator;
    }

    /**
     * @param ActorImportedFromUDB2 $actorImportedFromUDB2
     */
    public function applyActorImportedFromUDB2(
        ActorImportedFromUDB2 $actorImportedFromUDB2
    ) {
        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $actorImportedFromUDB2->getCdbXmlNamespaceUri(),
            $actorImportedFromUDB2->getCdbXml()
        );

        $document = $this->newDocument($actorImportedFromUDB2->getActorId());
        $eventLd = $document->getBody();

        $detail = null;

        $details = $udb2Actor->getDetails();

        foreach ($details as $languageDetail) {
          $language = $languageDetail->getLanguage();

          // The first language detail found will be used to retrieve
          // properties from which in UDB3 are not any longer considered
          // to be language specific.
          if (!$detail) {
            $detail = $languageDetail;
          }

          $eventLd->name[$language] = $languageDetail->getTitle();

        }

        $eventLd->addresses = array();
        $contact_cdb = $udb2Actor->getContactInfo();
        /** @var \CultureFeed_Cdb_Data_Address[] $addresses **/
        $addresses = $contact_cdb->getAddresses();

        foreach ($addresses as $address) {

          $address = $address->getPhysicalAddress();

          $eventLd->addresses[] = array(
            'addressCountry' => $address->getCountry(),
            'addressLocality' => $address->getCity(),
            'postalCode' => $address->getZip(),
            'streetAddress' => $address->getStreet() . ' ' . $address->getHouseNumber(),
          );
        }

        $eventLdModel = new JsonDocument(
            $actorImportedFromUDB2->getActorId()
        );

        $this->repository->save($eventLdModel->withBody($eventLd));
    }

    /**
     * @param EventCreated $eventCreated
     */
    protected function applyEventCreated(EventCreated $eventCreated)
    {
        // @todo This just creates an empty event. Should we do anything here?
    }

    /**
     * @param string $id
     * @return JsonDocument
     */
    protected function newDocument($id)
    {
        $document = new JsonDocument($id);

        $eventLd = $document->getBody();
        $eventLd->{'@id'} = $this->iriGenerator->iri($id);

        // @todo provide Event-LD context here relative to the base URI
        $eventLd->{'@context'} = '/api/1.0/place.jsonld';

        return $document->withBody($eventLd);
    }

    /**
     * @param ActorActor $actor
     * @return JsonDocument
     */
    protected function loadDocumentFromRepository(ActorActor $actor)
    {
        $document = $this->repository->get($actor->getActorId());

        if (!$document) {
            return $this->newDocument($actor->getActorId());
        }

        return $document;
    }
}
