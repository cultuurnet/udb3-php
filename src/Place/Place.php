<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Place\Place.
 */

namespace CultuurNet\UDB3\Place;

use CultuurNet\UDB3\Actor\Actor;
use src\Place\Events\PlaceImportedFromUDB2;

class Place extends Actor
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
        $place = new static();
        $place->apply(
            new PlaceImportedFromUDB2(
                $actorId,
                $cdbXml,
                $cdbXmlNamespaceUri
            )
        );

        return $place;
    }

    public function applyPlaceImportedFromUDB2(
        PlaceImportedFromUDB2 $placeImported
    ) {
        $this->applyActorImportedFromUDB2($placeImported);
    }
}
