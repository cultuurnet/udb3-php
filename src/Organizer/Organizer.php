<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Cdb\UpdateableWithCdbXmlInterface;
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
     * @param Url $website
     * @param Title $title
     * @param array $addresses
     * @param array $phones
     * @param array $emails
     * @param array $urls
     * @return Organizer
     */
    public static function create(
        $id,
        Url $website,
        Title $title,
        array $addresses,
        array $phones,
        array $emails,
        array $urls
    ) {
        $organizer = new self();
        $organizer->apply(
            new OrganizerCreatedWithUniqueWebsite($id, $website, $title, $addresses, $phones, $emails, $urls)
        );

        return $organizer;
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
     * @param OrganizerCreated $organizerCreated
     */
    protected function applyOrganizerCreatedWithUniqueWebsite(OrganizerCreatedWithUniqueWebsite $organizerCreated)
    {
        $this->actorId = $organizerCreated->getOrganizerId();
    }

    public function applyOrganizerImportedFromUDB2(
        OrganizerImportedFromUDB2 $organizerImported
    ) {
        $this->actorId = (string) $organizerImported->getActorId();
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
