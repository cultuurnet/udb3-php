<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventBusInterface;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use CultuurNet\UDB3\Actor\ActorLDProjector;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Organizer\Events\LabelAdded;
use CultuurNet\UDB3\Organizer\Events\LabelRemoved;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerDeleted;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Organizer\Events\OrganizerUpdatedFromUDB2;
use CultuurNet\UDB3\Organizer\ReadModel\JSONLD\CdbXMLImporter;
use CultuurNet\UDB3\ReadModel\JsonDocument;

class OrganizerLDProjector extends ActorLDProjector
{
    /**
     * @var CdbXMLImporter
     */
    private $cdbXMLImporter;

    /**
     * @var ReadRepositoryInterface
     */
    private $labelRepository;

    /**
     * OrganizerLDProjector constructor.
     * @param DocumentRepositoryInterface $repository
     * @param IriGeneratorInterface $iriGenerator
     * @param EventBusInterface $eventBus
     * @param ReadRepositoryInterface|null $labelRepository
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator,
        EventBusInterface $eventBus,
        ReadRepositoryInterface $labelRepository = null
    ) {
        parent::__construct(
            $repository,
            $iriGenerator,
            $eventBus
        );

        $this->cdbXMLImporter = new CdbXMLImporter();

        $this->labelRepository = $labelRepository;
    }

    /**
     * @param OrganizerImportedFromUDB2 $organizerImportedFromUDB2
     */
    public function applyOrganizerImportedFromUDB2(
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

        $this->publishJSONLDUpdated(
            $organizerImportedFromUDB2->getActorId()
        );
    }

    /**
     * @param OrganizerCreated $organizerCreated
     */
    protected function applyOrganizerCreated(OrganizerCreated $organizerCreated, DomainMessage $domainMessage)
    {
        $document = $this->newDocument($organizerCreated->getOrganizerId());

        $jsonLD = $document->getBody();

        $jsonLD->{'@id'} = $this->iriGenerator->iri(
            $organizerCreated->getOrganizerId()
        );

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
     * @param OrganizerUpdatedFromUDB2 $organizerUpdatedFromUDB2
     */
    public function applyOrganizerUpdatedFromUDB2(
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

        $this->publishJSONLDUpdated(
            $organizerUpdatedFromUDB2->getActorId()
        );
    }

    /**
     * @param LabelAdded $labelAdded
     */
    public function applyLabelAdded(LabelAdded $labelAdded)
    {
        $this->guardLabelRepository();

        $document = $this->repository->get($labelAdded->getOrganizerId());

        $jsonLD = $document->getBody();
        $labels = isset($jsonLD->labels) ? $jsonLD->labels : [];

        $label = $this->labelRepository->getByUuid($labelAdded->getLabelId());
        $labels[] = [
            'uuid' => $label->getUuid()->toNative(),
            'name' => $label->getName()->toNative()
        ];

        $jsonLD->labels = $labels;

        $this->repository->save($document->withBody($jsonLD));
    }

    /**
     * @param LabelRemoved $labelRemoved
     */
    public function applyLabelRemoved(LabelRemoved $labelRemoved)
    {
        $this->guardLabelRepository();

        $document = $this->repository->get($labelRemoved->getOrganizerId());
        $jsonLD = $document->getBody();

        if (isset($jsonLD->labels)) {
            $labels = $jsonLD->labels;

            for ($index = 0; $index < count($labels); $index++) {
                $label = $labels[$index];
                if ($label->uuid === $labelRemoved->getLabelId()->toNative()) {
                    unset($labels[$index]);
                    break;
                }
            }

            if (count($labels) === 0) {
                unset($jsonLD->labels);
            } else {
                $jsonLD->labels = $labels;
            }

            $this->repository->save($document->withBody($jsonLD));
        }
    }

    /**
     * @param \CultuurNet\UDB3\Organizer\Events\OrganizerDeleted $organizerDeleted
     */
    public function applyOrganizerDeleted(
        OrganizerDeleted $organizerDeleted
    ) {
        $this->repository->remove($organizerDeleted->getOrganizerId());
    }

    /**
     * @param string $id
     * @todo Move broadcasting functionality to a decorator.
     */
    protected function publishJSONLDUpdated($id)
    {
        $generator = new Version4Generator();
        $events = [
            DomainMessage::recordNow(
                $generator->generate(),
                1,
                new Metadata(),
                new OrganizerProjectedToJSONLD($id)
            )
        ];

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

    /**
     * @throws \InvalidArgumentException
     */
    private function guardLabelRepository()
    {
        if ($this->labelRepository === null) {
            throw new \InvalidArgumentException('A label repository is needed for the labelling events.');
        }
    }
}
