<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

class EventImportedFromUDB2 extends EventEvent
{
    /**
     * @var string
     */
    protected $cdbXml;

    /**
     * @var string
     */
    protected $cdbXmlNamespaceUri;

    /**
     * @param string $eventId
     * @param string $cdbXml
     * @param string $cdbXmlNamespaceUri
     */
    public function __construct($eventId, $cdbXml, $cdbXmlNamespaceUri)
    {
        parent::__construct($eventId);
        $this->cdbXml = $cdbXml;
        $this->cdbXmlNamespaceUri = $cdbXmlNamespaceUri;
    }

    public function getCdbXml()
    {
        return $this->cdbXml;
    }

    /**
     * @return string
     */
    public function getCdbXmlNamespaceUri()
    {
        return $this->cdbXmlNamespaceUri;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'cdbxml' => $this->cdbXml,
            'cdbXmlNamespaceUri' => $this->cdbXmlNamespaceUri,
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
