<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Organizer\Organizer.
 */

namespace CultuurNet\UDB3\Organizer;

use CultuurNet\UDB3\Actor\Actor;
use CultuurNet\UDB3\Cdb\UpdateableWithCdbXmlInterface;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Organizer\Events\OrganizerUpdatedFromUDB2;
use CultuurNet\UDB3\Place\Place;
use CultuurNet\UDB3\Title;
use ValueObjects\String\String;

class Organizer extends Actor implements UpdateableWithCdbXmlInterface
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
     * @return Actor
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
     * @todo Refactor this method so it can be called create. Currently the
     * normal behavior for create is taken by the legacy udb2 logic.
     *
     * @param String $id
     * @param Title $title
     * @param array $addresses
     * @param array $phones
     * @param array $emails
     * @param array $urls
     *
     * @return Place
     */
    public static function createOrganizer($id, Title $title, array $addresses, array $phones, array $emails, array $urls)
    {
        $organizer = new self();
        $organizer->apply(new OrganizerCreated($id, $title, $addresses, $phones, $emails, $urls));

        return $organizer;
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
        $this->applyActorImportedFromUDB2($organizerImported);
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
