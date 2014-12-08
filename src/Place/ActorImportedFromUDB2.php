<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Place\ActorActor.
 */

namespace CultuurNet\UDB3\Place;

class ActorImportedFromUDB2 extends ActorActor
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
    public function __construct($actorId, $cdbXml, $cdbXmlNamespaceUri)
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
            'cdbXmlNamespaceUri' => \CultureFeed_Cdb_Default::CDB_SCHEME_URL
        );
        return new static(
            $data['actor_id'],
            $data['cdbxml'],
            $data['cdbXmlNamespaceUri']
        );
    }
}
