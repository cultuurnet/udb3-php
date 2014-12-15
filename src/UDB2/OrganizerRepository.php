<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\UDB2\OrganizerRepository.
 */

namespace CultuurNet\UDB3\UDB2;

use CultuurNet\Search\Parameter\FilterQuery;
use CultuurNet\UDB3\Organizer\Organizer;
use CultuurNet\UDB3\Actor\ActorImportedFromUDB2;
use CultuurNet\Search\Parameter\Query;

/**
 * Repository decorator that first updates UDB2.
 *
 * When a failure on UDB2 occurs, the whole transaction will fail.
 */
class OrganizerRepository extends ActorRepository {

    /**
     * Returns the type.
     * @return string
     */
    protected function getType() {
        return '\\CultuurNet\\UDB3\\Organizer\\Organizer';
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
    protected function importFromUDB2($id, $actorXml, $cdbSchemeUrl) {

        return Organizer::importFromUDB2(
            $id,
            $actorXml,
            $cdbSchemeUrl
        );
    }

}
