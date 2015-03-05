<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use CultuurNet\UDB3\Organizer\Organizer;

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
