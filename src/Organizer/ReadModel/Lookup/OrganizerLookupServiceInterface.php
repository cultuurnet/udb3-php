<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Organizer\ReadModel\Lookup;

interface OrganizerLookupServiceInterface
{
    /**
     * @param string $part
     *
     * @return string[]
     *   A list of organizer IDs.
     */
    public function findOrganizersByPartOfTitle($part);
}
