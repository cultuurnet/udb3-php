<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Place\PlaceLDProjector.
 */

namespace CultuurNet\UDB3\Place;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use CultuurNet\UDB3\Actor\ActorImportedFromUDB2;
use CultuurNet\UDB3\Actor\ActorLDProjector;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\Place\ReadModel\JSONLD\CdbXMLImporter;

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
}
