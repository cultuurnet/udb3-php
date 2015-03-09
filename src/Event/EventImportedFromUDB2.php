<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\HasCdbXmlTrait;

class EventImportedFromUDB2 extends EventEvent
{
    use HasCdbXmlTrait;

    /**
     * @param string $eventId
     * @param string $cdbXml
     * @param string $cdbXmlNamespaceUri
     */
    public function __construct($eventId, $cdbXml, $cdbXmlNamespaceUri)
    {
        parent::__construct($eventId);
        $this->setCdbXml($cdbXml);
        $this->setCdbXmlNamespaceUri($cdbXmlNamespaceUri);
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'cdbxml' => $this->getCdbXml(),
            'cdbXmlNamespaceUri' => $this->getCdbXmlNamespaceUri(),
        );
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        $data += array(
            'cdbXmlNamespaceUri' => \CultureFeed_Cdb_Default::CDB_SCHEME_URL
        );
        return new static(
            $data['event_id'],
            $data['cdbxml'],
            $data['cdbXmlNamespaceUri']
        );
    }
}
