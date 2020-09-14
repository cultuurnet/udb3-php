<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Actor\ActorImportedFromUDB2.
 */

namespace CultuurNet\UDB3\Actor;

class ActorImportedFromUDB2 extends ActorEvent
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
     * @param string $actorId
     * @param string $cdbXml
     * @param string $cdbXmlNamespaceUri
     */
    final public function __construct(string $actorId, string $cdbXml, string $cdbXmlNamespaceUri)
    {
        parent::__construct($actorId);
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
            'cdbXmlNamespaceUri' => \CultureFeed_Cdb_Xml::namespaceUriForVersion('3.2'),
        );
        return new static(
            $data['actor_id'],
            $data['cdbxml'],
            $data['cdbXmlNamespaceUri']
        );
    }
}
