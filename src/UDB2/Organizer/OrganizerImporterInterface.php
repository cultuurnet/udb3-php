<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\Organizer;

use CultuurNet\UDB3\Organizer\Organizer;

/**
 * Imports organizers from UDB2 (where they are called 'actors') into UDB3.
 */
interface OrganizerImporterInterface
{
    /**
     * @param string $organizerId
     * @return Organizer
     */
    public function updateOrganizerFromUDB2($organizerId);

    /**P
     * @param string $organizerId
     * @return Organizer
     */
    public function createOrganizerFromUDB2($organizerId);
}
