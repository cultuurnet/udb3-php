<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Cdb\UpdateableWithCdbXmlInterface;
use CultuurNet\UDB3\Organizer\Events\LabelAdded;
use CultuurNet\UDB3\Organizer\Events\LabelRemoved;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerDeleted;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Organizer\Events\OrganizerUpdatedFromUDB2;
use CultuurNet\UDB3\Title;
use ValueObjects\Identity\UUID;

class Organizer extends EventSourcedAggregateRoot implements UpdateableWithCdbXmlInterface
{
    /**
     * The actor id.
     *
     * @var string
     */
    protected $actorId;

    /**
     * @var UUID[]
     */
    private $labelIds = [];

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootId()
    {
        return $this->actorId;
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
     * @param String $id
     * @param Title $title
     * @param array $addresses
     * @param array $phones
     * @param array $emails
     * @param array $urls
     *
     * @return Organizer
     */
    public static function create($id, Title $title, array $addresses, array $phones, array $emails, array $urls)
    {
        $organizer = new self();
        $organizer->apply(new OrganizerCreated($id, $title, $addresses, $phones, $emails, $urls));

        return $organizer;
    }

    /**
     * @param UUID $labelId
     */
    public function addLabel(UUID $labelId)
    {
        if (!in_array($labelId, $this->labelIds)) {
            $this->apply(new LabelAdded($this->actorId, $labelId));
        }
    }

    /**
     * @param UUID $labelId
     */
    public function removeLabel(UUID $labelId)
    {
        if (in_array($labelId, $this->labelIds)) {
            $this->apply(new LabelRemoved($this->actorId, $labelId));
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

    public function applyOrganizerImportedFromUDB2(
        OrganizerImportedFromUDB2 $organizerImported
    ) {
        $this->actorId = (string) $organizerImported->getActorId();
    }

    /**
     * @param LabelAdded $labelAdded
     */
    public function applyLabelAdded(LabelAdded $labelAdded)
    {
        $this->labelIds[] = $labelAdded->getLabelId();
    }

    /**
     * @param LabelRemoved $labelRemoved
     */
    public function applyLabelRemoved(LabelRemoved $labelRemoved)
    {
        $labelId = $labelRemoved->getLabelId();
        $this->labelIds = array_diff($this->labelIds, [$labelId]);
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
}
