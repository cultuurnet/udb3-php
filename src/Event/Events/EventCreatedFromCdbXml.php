<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 07/10/15
 * Time: 11:25
 */

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\XmlString;
use ValueObjects\String\String;

class EventCreatedFromCdbXml implements SerializableInterface
{
    /**
     * @var XMLString
     */
    protected $xmlString;

    /**
     * @var String
     */
    protected $eventId;

    /**
     * @var String
     */
    protected $cdbXmlNamespaceUri;

    public function __construct(String $eventId, XmlString $xmlString, String $cdbXmlNamespaceUri)
    {
        $this->xmlString = $xmlString;
        $this->eventId = $eventId;
        $this->cdbXmlNamespaceUri = $cdbXmlNamespaceUri;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static(
            new String($data['event_id']),
            new XmlString($data['cdbxml']),
            new String($data['cdbXmlNamespaceUri'])
        );
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'event_id' => $this->eventId->toNative(),
            'cdbxml' => $this->xmlString->toNative(),
            'cdbXmlNamespaceUri' => $this->cdbXmlNamespaceUri->toNative());
    }

    /**
     * @return XmlString
     */
    public function getXmlString()
    {
        return $this->xmlString;
    }

    /**
     * @return String
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @return String
     */
    public function getCdbXmlNamespaceUri()
    {
        return $this->cdbXmlNamespaceUri;
    }
}
