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

class PlaceLDProjector extends ActorLDProjector
{
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
        $actorLd = $document->getBody();

        $detail = null;

        // Details.
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
        $actorLd->description = $detail->getLongDescription();

        // Address
        $contact_cdb = $udb2Actor->getContactInfo();
        /** @var \CultureFeed_Cdb_Data_Address[] $addresses */
        $addresses = $contact_cdb->getAddresses();

        foreach ($addresses as $address) {
            $address = $address->getPhysicalAddress();

            if ($address) {
                $actorLd->address = array(
                    'addressCountry' => $address->getCountry(),
                    'addressLocality' => $address->getCity(),
                    'postalCode' => $address->getZip(),
                    'streetAddress' => $address->getStreet(
                        ) . ' ' . $address->getHouseNumber(),
                );

                break;
            }
        }

        // Booking info.
        $bookingInfo = array(
            'description' => '',
            'name' => 'standard price',
            'price' => 0.0,
            'priceCurrency' => 'EUR',
        );
        $price = $detail->getPrice();

        if ($price) {
            $bookingInfo['description'] = floatval($price->getDescription());
            $bookingInfo['name'] = floatval($price->getTitle());
            $bookingInfo['price'] = floatval($price->getValue());
        }
        $actorLd->bookingInfo = $bookingInfo;


        // Image.
        $images = $detail->getMedia()->byMediaType(
            \CultureFeed_Cdb_Data_File::MEDIA_TYPE_PHOTO
        );
        $images->rewind();
        $image = count($images) > 0 ? $images->current() : null;
        $actorLd->image = $image ? $image->getHLink() : null;

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
