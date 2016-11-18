<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Cdb\UpdateableWithCdbXmlInterface;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Organizer\Events\AddressUpdated;
use CultuurNet\UDB3\Organizer\Events\ContactPointUpdated;
use CultuurNet\UDB3\Organizer\Events\LabelAdded;
use CultuurNet\UDB3\Organizer\Events\LabelRemoved;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreatedWithUniqueWebsite;
use CultuurNet\UDB3\Organizer\Events\OrganizerDeleted;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Organizer\Events\OrganizerUpdatedFromUDB2;
use CultuurNet\UDB3\Title;
use ValueObjects\Web\Url;

class Organizer extends EventSourcedAggregateRoot implements UpdateableWithCdbXmlInterface
{
    /**
     * The actor id.
     *
     * @var string
     */
    protected $actorId;

    /**
     * @var Address|null
     */
    private $address;

    /**
     * @var ContactPoint
     */
    private $contactPoint;

    /**
     * @var Label[]
     */
    private $labels = [];

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootId()
    {
        return $this->actorId;
    }

    public function __construct()
    {
        // Contact points can be empty, but we only want to start recording
        // ContactPointUpdated events as soon as the organizer is updated
        // with a non-empty contact point. To enforce this we initialize the
        // aggregate state with an empty contact point.
        $this->contactPoint = new ContactPoint();
    }

    /**
     * Import from UDB2.
     *
     * @param string $actorId
     *   The actor id.
     * @param string $cdbXml
     *   The cdb xml.
     * @param string $cdbXmlNamespaceUri
     *   The cdb xml namespace uri.
     *
     * @return Organizer
     *   The actor.
     */
    public static function importFromUDB2(
        $actorId,
        $cdbXml,
        $cdbXmlNamespaceUri
    ) {
        $organizer = new static();
        $organizer->apply(
            new OrganizerImportedFromUDB2(
                $actorId,
                $cdbXml,
                $cdbXmlNamespaceUri
            )
        );

        return $organizer;
    }

    /**
     * Factory method to create a new Organizer.
     *
     * @param string $id
     * @param Url $website
     * @param Title $title
     * @return Organizer
     */
    public static function create(
        $id,
        Url $website,
        Title $title
    ) {
        $organizer = new self();

        $organizer->apply(
            new OrganizerCreatedWithUniqueWebsite($id, $website, $title)
        );

        return $organizer;
    }

    /**
     * @inheritdoc
     */
    public function updateWithCdbXml($cdbXml, $cdbXmlNamespaceUri)
    {
        $this->apply(
            new OrganizerUpdatedFromUDB2(
                $this->actorId,
                $cdbXml,
                $cdbXmlNamespaceUri
            )
        );
    }

    /**
     * @param Address $address
     */
    public function updateAddress(Address $address)
    {
        if (is_null($this->address) || !$this->address->sameAs($address)) {
            $this->apply(
                new AddressUpdated($this->actorId, $address)
            );
        }
    }

    /**
     * @param ContactPoint $contactPoint
     */
    public function updateContactPoint(ContactPoint $contactPoint)
    {
        if (!$this->contactPoint->sameAs($contactPoint)) {
            $this->apply(
                new ContactPointUpdated($this->actorId, $contactPoint)
            );
        }
    }

    /**
     * @param Label $label
     */
    public function addLabel(Label $label)
    {
        if (!in_array($label, $this->labels)) {
            $this->apply(new LabelAdded($this->actorId, $label));
        }
    }

    /**
     * @param Label $label
     */
    public function removeLabel(Label $label)
    {
        if (in_array($label, $this->labels)) {
            $this->apply(new LabelRemoved($this->actorId, $label));
        }
    }

    public function delete()
    {
        $this->apply(
            new OrganizerDeleted($this->getAggregateRootId())
        );
    }

    /**
     * Apply the organizer created event.
     * @param OrganizerCreated $organizerCreated
     */
    protected function applyOrganizerCreated(OrganizerCreated $organizerCreated)
    {
        $this->actorId = $organizerCreated->getOrganizerId();
    }

    /**
     * Apply the organizer created event.
     * @param OrganizerCreatedWithUniqueWebsite $organizerCreated
     */
    protected function applyOrganizerCreatedWithUniqueWebsite(OrganizerCreatedWithUniqueWebsite $organizerCreated)
    {
        $this->actorId = $organizerCreated->getOrganizerId();
    }

    /**
     * @param OrganizerImportedFromUDB2 $organizerImported
     */
    protected function applyOrganizerImportedFromUDB2(
        OrganizerImportedFromUDB2 $organizerImported
    ) {
        $this->actorId = (string) $organizerImported->getActorId();

        $actor = ActorItemFactory::createActorFromCdbXml(
            $organizerImported->getCdbXmlNamespaceUri(),
            $organizerImported->getCdbXml()
        );

        $this->setLabelsFromUDB2Item($actor);
    }

    /**
     * @param OrganizerUpdatedFromUDB2 $organizerUpdatedFromUDB2
     */
    protected function applyOrganizerUpdatedFromUDB2(
        OrganizerUpdatedFromUDB2 $organizerUpdatedFromUDB2
    ) {
        $actor = ActorItemFactory::createActorFromCdbXml(
            $organizerUpdatedFromUDB2->getCdbXmlNamespaceUri(),
            $organizerUpdatedFromUDB2->getCdbXml()
        );

        $this->setLabelsFromUDB2Item($actor);
    }

    /**
     * @param AddressUpdated $addressUpdated
     */
    protected function applyAddressUpdated(AddressUpdated $addressUpdated)
    {
        $this->address = $addressUpdated->getAddress();
    }

    /**
     * @param ContactPointUpdated $contactPointUpdated
     */
    protected function applyContactPointUpdated(ContactPointUpdated $contactPointUpdated)
    {
        $this->contactPoint = $contactPointUpdated->getContactPoint();
    }

    /**
     * @param LabelAdded $labelAdded
     */
    protected function applyLabelAdded(LabelAdded $labelAdded)
    {
        $this->labels[] = $labelAdded->getLabel();
    }

    /**
     * @param LabelRemoved $labelRemoved
     */
    protected function applyLabelRemoved(LabelRemoved $labelRemoved)
    {
        $label = $labelRemoved->getLabel();
        $this->labels = array_diff($this->labels, [$label]);
    }

    /**
     * @param \CultureFeed_Cdb_Item_Base $udb2Item
     */
    protected function setLabelsFromUDB2Item(\CultureFeed_Cdb_Item_Base $udb2Item)
    {
        $this->labels = [];

        /** @var \CultureFeed_Cdb_Data_Keyword $udb2Keyword */
        foreach (array_values($udb2Item->getKeywords(true)) as $udb2Keyword) {
            $keyword = trim($udb2Keyword->getValue());
            if ($keyword) {
                $this->labels[] = new Label($keyword, $udb2Keyword->isVisible());
            }
        }
    }
}
