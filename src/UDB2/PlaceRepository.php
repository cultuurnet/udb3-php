<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\UDB2\PlaceRepository.
 */

namespace CultuurNet\UDB3\UDB2;

use CultuurNet\UDB3\Actor\ActorImportedFromUDB2;
use CultuurNet\UDB3\Place\Place;

/**
 * Repository decorator that first updates UDB2.
 *
 * When a failure on UDB2 occurs, the whole transaction will fail.
 */
class PlaceRepository extends ActorRepository
{
    /**
     * Returns the type.
     * @return string
     */
    protected function getType()
    {
        return Place::class;
    }

    /**
     * Imports from UDB2.
     *
     * @param string $id
     *   The id.
     * @param string $actorXml
     *   The actor xml.
     * @param string $cdbSchemeUrl
     *
     * @return ActorImportedFromUDB2
     */
    protected function importFromUDB2($id, $actorXml, $cdbSchemeUrl)
    {
        return Place::importFromUDB2(
            $id,
            $actorXml,
            $cdbSchemeUrl
        );
    }
}
