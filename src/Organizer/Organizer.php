<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Organizer\Organizer.
 */

namespace CultuurNet\UDB3\Organizer;

use CultuurNet\UDB3\Actor\Actor;
use CultuurNet\UDB3\Place\Events\OrganizerImportedFromUDB2;

class Organizer extends Actor
{
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

    public function applyOrganizerImportedFromUDB2(
      OrganizerImportedFromUDB2 $organizerImported
    ) {
        $this->applyActorImportedFromUDB2($organizerImported);
    }
}
