<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Actor\ActorEvent;
use CultuurNet\UDB3\Actor\ActorLDProjector;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Organizer\Events\AddressUpdated;
use CultuurNet\UDB3\Organizer\Events\ContactPointUpdated;
use CultuurNet\UDB3\Organizer\Events\LabelAdded;
use CultuurNet\UDB3\Organizer\Events\LabelRemoved;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreatedWithUniqueWebsite;
use CultuurNet\UDB3\Organizer\Events\OrganizerDeleted;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Organizer\Events\OrganizerUpdatedFromUDB2;
use CultuurNet\UDB3\Organizer\Events\TitleUpdated;
use CultuurNet\UDB3\Organizer\Events\WebsiteUpdated;
use CultuurNet\UDB3\Organizer\ReadModel\JSONLD\CdbXMLImporter;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\ReadModel\MultilingualJsonLDProjectorTrait;

class OrganizerLDProjector implements EventListenerInterface
{
    use MultilingualJsonLDProjectorTrait;
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var DocumentRepositoryInterface
     */
    protected $repository;

    /**
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * @var EventBusInterface
     */
    protected $eventBus;

    /**
     * @var CdbXMLImporter
     */
    private $cdbXMLImporter;

    /**
     * @param DocumentRepositoryInterface $repository
     * @param IriGeneratorInterface $iriGenerator
     * @param EventBusInterface $eventBus
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator,
        EventBusInterface $eventBus
    ) {
        $this->repository = $repository;
        $this->iriGenerator = $iriGenerator;
        $this->eventBus = $eventBus;
        $this->cdbXMLImporter = new CdbXMLImporter();
    }

    /**
     * @param OrganizerImportedFromUDB2 $organizerImportedFromUDB2
     */
    private function applyOrganizerImportedFromUDB2(
        OrganizerImportedFromUDB2 $organizerImportedFromUDB2
    ) {
        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $organizerImportedFromUDB2->getCdbXmlNamespaceUri(),
            $organizerImportedFromUDB2->getCdbXml()
        );

        $document = $this->newDocument($organizerImportedFromUDB2->getActorId());
        $actorLd = $document->getBody();

        $actorLd = $this->cdbXMLImporter->documentWithCdbXML(
            $actorLd,
            $udb2Actor
        );

        $this->repository->save($document->withBody($actorLd));
    }

    /**
     * @param OrganizerCreated $organizerCreated
     * @param DomainMessage $domainMessage
     */
    private function applyOrganizerCreated(OrganizerCreated $organizerCreated, DomainMessage $domainMessage)
    {
        $document = $this->newDocument($organizerCreated->getOrganizerId());

        $jsonLD = $document->getBody();

        $jsonLD->{'@id'} = $this->iriGenerator->iri(
            $organizerCreated->getOrganizerId()
        );

        $this->setMainLanguage($jsonLD, new Language('nl'));

        $jsonLD->name = $organizerCreated->getTitle();

        $addresses = $organizerCreated->getAddresses();
        $jsonLD->addresses = array();
        foreach ($addresses as $address) {
            $jsonLD->addresses[] = array(
                'addressCountry' => $address->getCountry(),
                'addressLocality' => $address->getLocality(),
                'postalCode' => $address->getPostalCode(),
                'streetAddress' => $address->getStreetAddress(),
            );
        }

        $jsonLD->phone = $organizerCreated->getPhones();
        $jsonLD->email = $organizerCreated->getEmails();
        $jsonLD->url = $organizerCreated->getUrls();

        $recordedOn = $domainMessage->getRecordedOn()->toString();
        $jsonLD->created = \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $recordedOn
        )->format('c');

        $metaData = $domainMessage->getMetadata()->serialize();
        if (isset($metaData['user_id']) && isset($metaData['user_nick'])) {
            $jsonLD->creator = "{$metaData['user_id']} ({$metaData['user_nick']})";
        }

        $this->repository->save($document->withBody($jsonLD));
    }

    /**
     * @param OrganizerCreatedWithUniqueWebsite $organizerCreated
     * @param DomainMessage $domainMessage
     */
    private function applyOrganizerCreatedWithUniqueWebsite(
        OrganizerCreatedWithUniqueWebsite $organizerCreated,
        DomainMessage $domainMessage
    ) {
        $document = $this->newDocument($organizerCreated->getOrganizerId());

        $jsonLD = $document->getBody();

        $jsonLD->{'@id'} = $this->iriGenerator->iri(
            $organizerCreated->getOrganizerId()
        );

        $this->setMainLanguage($jsonLD, new Language('nl'));

        $jsonLD->url = (string) $organizerCreated->getWebsite();
        $jsonLD->name = $organizerCreated->getTitle();

        $recordedOn = $domainMessage->getRecordedOn()->toString();
        $jsonLD->created = \DateTime::createFromFormat(
            DateTime::FORMAT_STRING,
            $recordedOn
        )->format('c');

        $metaData = $domainMessage->getMetadata()->serialize();
        if (isset($metaData['user_id']) && isset($metaData['user_nick'])) {
            $jsonLD->creator = "{$metaData['user_id']} ({$metaData['user_nick']})";
        }

        $this->repository->save($document->withBody($jsonLD));
    }

    /**
     * @param WebsiteUpdated $websiteUpdated
     */
    private function applyWebsiteUpdated(WebsiteUpdated $websiteUpdated)
    {
        $organizerId = $websiteUpdated->getOrganizerId();

        $document = $this->repository->get($organizerId);

        $jsonLD = $document->getBody();
        $jsonLD->url = (string) $websiteUpdated->getWebsite();

        $this->repository->save($document->withBody($jsonLD));
    }

    /**
     * @param TitleUpdated $titleUpdated
     */
    private function applyTitleUpdated(TitleUpdated $titleUpdated)
    {
        $organizerId = $titleUpdated->getOrganizerId();

        $document = $this->repository->get($organizerId);

        $jsonLD = $document->getBody();
        $jsonLD->name = $titleUpdated->getTitle()->toNative();

        $this->repository->save($document->withBody($jsonLD));
    }

    /**
     * @param AddressUpdated $addressUpdated
     */
    private function applyAddressUpdated(AddressUpdated $addressUpdated)
    {
        $organizerId = $addressUpdated->getOrganizerId();
        $address = $addressUpdated->getAddress();

        $document = $this->repository->get($organizerId);

        $jsonLD = $document->getBody();
        $jsonLD->address = $address->toJsonLd();

        $this->repository->save($document->withBody($jsonLD));
    }

    /**
     * @param ContactPointUpdated $contactPointUpdated
     */
    private function applyContactPointUpdated(ContactPointUpdated $contactPointUpdated)
    {
        $organizerId = $contactPointUpdated->getOrganizerId();
        $contactPoint = $contactPointUpdated->getContactPoint();

        $document = $this->repository->get($organizerId);

        $jsonLD = $document->getBody();
        $jsonLD->contactPoint = $contactPoint->toJsonLd();

        $this->repository->save($document->withBody($jsonLD));
    }

    /**
     * @param OrganizerUpdatedFromUDB2 $organizerUpdatedFromUDB2
     */
    private function applyOrganizerUpdatedFromUDB2(
        OrganizerUpdatedFromUDB2 $organizerUpdatedFromUDB2
    ) {
        // It's possible that an organizer has been deleted in udb3, but never
        // in udb2. If an update comes for that organizer from udb2, it should
        // be imported again. This is intended by design.
        // @see https://jira.uitdatabank.be/browse/III-1092
        try {
            $document = $this->loadDocumentFromRepository(
                $organizerUpdatedFromUDB2
            );
        } catch (DocumentGoneException $e) {
            $document = $this->newDocument($organizerUpdatedFromUDB2->getActorId());
        }

        $udb2Actor = ActorItemFactory::createActorFromCdbXml(
            $organizerUpdatedFromUDB2->getCdbXmlNamespaceUri(),
            $organizerUpdatedFromUDB2->getCdbXml()
        );

        $actorLd = $this->cdbXMLImporter->documentWithCdbXML(
            $document->getBody(),
            $udb2Actor
        );

        $this->repository->save($document->withBody($actorLd));
    }

    /**
     * @param LabelAdded $labelAdded
     */
    private function applyLabelAdded(LabelAdded $labelAdded)
    {
        $document = $this->repository->get($labelAdded->getOrganizerId());

        $jsonLD = $document->getBody();

        // Check the visibility of the label to update the right property.
        $labelsProperty = $labelAdded->getLabel()->isVisible() ? 'labels' : 'hiddenLabels';

        $labels = isset($jsonLD->{$labelsProperty}) ? $jsonLD->{$labelsProperty} : [];
        $label = (string) $labelAdded->getLabel();

        $labels[] = $label;
        $jsonLD->{$labelsProperty} = array_unique($labels);

        $this->repository->save($document->withBody($jsonLD));
    }

    /**
     * @param LabelRemoved $labelRemoved
     */
    private function applyLabelRemoved(LabelRemoved $labelRemoved)
    {
        $document = $this->repository->get($labelRemoved->getOrganizerId());
        $jsonLD = $document->getBody();

        // Don't presume that the label visibility is correct when removing.
        // So iterate over both the visible and invisible labels.
        $labelsProperties = ['labels', 'hiddenLabels'];

        foreach ($labelsProperties as $labelsProperty) {
            if (isset($jsonLD->{$labelsProperty}) && is_array($jsonLD->{$labelsProperty})) {
                $jsonLD->{$labelsProperty} = array_filter(
                    $jsonLD->{$labelsProperty},
                    function ($label) use ($labelRemoved) {
                        return !$labelRemoved->getLabel()->equals(
                            new Label($label)
                        );
                    }
                );

                // Ensure array keys start with 0 so json_encode() does encode it
                // as an array and not as an object.
                if (count($jsonLD->{$labelsProperty}) > 0) {
                    $jsonLD->{$labelsProperty} = array_values($jsonLD->{$labelsProperty});
                } else {
                    unset($jsonLD->{$labelsProperty});
                }
            }
        }

        $this->repository->save($document->withBody($jsonLD));
    }

    /**
     * @param OrganizerDeleted $organizerDeleted
     */
    private function applyOrganizerDeleted(
        OrganizerDeleted $organizerDeleted
    ) {
        $this->repository->remove($organizerDeleted->getOrganizerId());
    }

    /**
     * @param string $id
     * @return JsonDocument
     */
    private function newDocument($id)
    {
        $document = new JsonDocument($id);

        $organizerLd = $document->getBody();
        $organizerLd->{'@id'} = $this->iriGenerator->iri($id);
        $organizerLd->{'@context'} = '/contexts/organizer';

        return $document->withBody($organizerLd);
    }

    /**
     * @param ActorEvent $actor
     * @return JsonDocument
     */
    private function loadDocumentFromRepository(ActorEvent $actor)
    {
        $document = $this->repository->get($actor->getActorId());

        if (!$document) {
            return $this->newDocument($actor->getActorId());
        }

        return $document;
    }

    /**
     * Returns an iri.
     *
     * @param string $id
     *   The id.
     *
     * @return string
     */
    private function iri($id)
    {
        return $this->iriGenerator->iri($id);
    }
}
