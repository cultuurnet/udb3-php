<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\Organizer;

use CultuurNet\UDB3\Organizer\Organizer;
use CultuurNet\UDB3\UDB2\Actor\ActorFactoryInterface;

/**
 * Creates UDB3 organizer entities based on UDB2 actor cdb xml.
 */
class OrganizerFactory implements ActorFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createFromCdbXml($id, $cdbXml, $cdbXmlNamespaceUri)
    {
        return Organizer::importFromUDB2(
            $id,
            $cdbXml,
            $cdbXmlNamespaceUri
        );
    }
}
